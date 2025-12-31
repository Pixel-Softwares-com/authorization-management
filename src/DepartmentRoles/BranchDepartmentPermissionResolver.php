<?php

namespace AuthorizationManagement\DepartmentRoles;

use AuthorizationManagement\AuthorizationModelManager;
use AuthorizationManagement\DepartmentRoles\Traits\HqRoleChecker;
use AuthorizationManagement\DepartmentRoles\Traits\BranchRoleChecker;
use AuthorizationManagement\DepartmentRoles\Traits\CheckViewAsPermissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Branch Department Permission Resolver Service
 * 
 * Resolves user permissions for filtered branches based on their department roles.
 * This provides a cleaner, more maintainable service-based approach for
 * checking department role permissions.
 */
class BranchDepartmentPermissionResolver
{
    use HqRoleChecker, BranchRoleChecker, CheckViewAsPermissions;

    protected ?int $userId = null;
    protected array $filteredBranchIds = [];
    protected ?string $departmentName = null;

    /**
     * Create a new permission resolver instance
     */
    public function __construct(
        ?int $userId = null,
        array $filteredBranchIds = [],
        ?string $departmentName = null
    ) {
        $this->userId = $userId ?? Auth::id();
        $this->filteredBranchIds = $filteredBranchIds;
        $this->departmentName = $departmentName;
    }

    /**
     * Resolve permissions for the user
     * 
     * @param array|null $roleOverrides An array of role configurations, or null to use defaults.
     */
    public function resolve(?array $roleOverrides = null): array
    {
        $roles = $roleOverrides ? collect($roleOverrides) : $this->getDefaultRoles();

        $permissions = [];

        // Add primary branch to filtered branches if not present
        $this->ensurePrimaryBranchInList();

        $mainBranchId = AuthorizationModelManager::getMainBranchId();

        // Check HQ permissions if main branch is in filtered list
        if (in_array($mainBranchId, $this->filteredBranchIds)) {
            $permissions = array_merge(
                $permissions,
                $this->resolveHqPermissions($roles)
            );
        }

        // Check branch permissions for non-HQ branches
        $branchPermissions = $this->resolveBranchPermissions($roles);
        $permissions = array_merge($permissions, $branchPermissions);

        return $permissions;
    }

    /**
     * Get default roles from registry
     */
    protected function getDefaultRoles(): Collection
    {
        return DepartmentRoleRegistry::getDefaultRoles();
    }

    /**
     * Ensure primary branch is in the filtered list
     */
    protected function ensurePrimaryBranchInList(): void
    {
        $user = Auth::user();
        $primaryBranchId = $user->branch_id ?? null;

        if ($primaryBranchId && !in_array($primaryBranchId, $this->filteredBranchIds)) {
            $this->filteredBranchIds[] = $primaryBranchId;
        }
    }

    /**
     * Resolve HQ (Headquarters) permissions
     */
    protected function resolveHqPermissions(Collection $roles): array
    {
        $permissions = [];

        foreach ($roles as $role) {
            $relationName = $role['relation'];
            $constantPrefix = $role['view_as_constant_prefix'];

            $permissionKey = "is_hq_" . strtolower($constantPrefix);

            $permissions[$permissionKey] = $this->hqRoleChecker(
                [$relationName],
                $this->departmentName ?? 'Electric'
            );
        }

        return $permissions;
    }

    /**
     * Resolve branch-specific permissions
     */
    protected function resolveBranchPermissions(Collection $roles): array
    {
        $permissions = [];
        $mainBranchId = AuthorizationModelManager::getMainBranchId();

        // Get branches excluding main branch
        $branches = array_diff($this->filteredBranchIds, [$mainBranchId]);

        foreach ($branches as $branchId) {
            foreach ($roles as $role) {
                $relationName = $role['relation'];
                $constantPrefix = $role['view_as_constant_prefix'];

                $permissionKey = "is_{$branchId}_" . strtolower($constantPrefix);

                $permissions[$permissionKey] = $this->branchRoleChecker(
                    $branchId,
                    [$relationName],
                    $this->departmentName ?? 'Electric'
                );
            }
        }

        return $permissions;
    }

    /**
     * Resolve permissions with custom role configuration
     * 
     * @param array $customRoleSettings Example: ['managers' => ['enabled' => true]]
     * @param bool $useDefaultRoles Whether to include all default roles or only the specified ones
     * @return array
     */
    public function resolveWithCustomSettings(
        array $customRoleSettings,
        bool $useDefaultRoles = true
    ): array {
        $roles = $this->buildRolesFromSettings($customRoleSettings, $useDefaultRoles);
        return $this->resolve($roles->toArray());
    }

    /**
     * Build roles collection from custom settings
     */
    protected function buildRolesFromSettings(
        array $customRoleSettings,
        bool $useDefaultRoles
    ): Collection {
        $roles = $useDefaultRoles ? $this->getDefaultRoles() : collect();

        if (empty($customRoleSettings)) {
            return $roles;
        }

        $filteredRoles = collect();

        foreach ($customRoleSettings as $relationName => $setting) {
            if (isset($setting['enabled']) && $setting['enabled'] === false) {
                continue;
            }

            $role = DepartmentRoleRegistry::getByRelation($relationName);
            
            if ($role) {
                $filteredRoles->put($role['key'], $role);
            }
        }

        return $filteredRoles;
    }

    /**
     * Set department name for checking
     */
    public function forDepartment(string $departmentName): self
    {
        $this->departmentName = $departmentName;
        return $this;
    }

    /**
     * Set filtered branch IDs
     */
    public function forBranches(array $branchIds): self
    {
        $this->filteredBranchIds = $branchIds;
        return $this;
    }

    /**
     * Set user ID
     */
    public function forUser(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Static factory method for fluent interface
     */
    public static function make(?int $userId = null): self
    {
        return new static($userId);
    }

    /**
     * Get creator permission check
     */
    public function checkCreatorPermission(int $creatorId): bool
    {
        return $this->userId === $creatorId;
    }

    /**
     * Check if user can add/create based on branch assignment and roles
     */
    public function canAdd(?int $branchId = null, ?array $requiredRoles = null): bool
    {
        $user = Auth::user();

        if (!$user->branch_id) {
            return false;
        }

        $mainBranchId = AuthorizationModelManager::getMainBranchId();

        if ($branchId !== null) {
            if ($branchId === $mainBranchId) {
                return $this->hasAnyHqRole($requiredRoles);
            }

            return $this->hasAnyBranchRole($branchId, $requiredRoles);
        }

        $hasViewAsPermission = $this->checkViewAsPermissions(
            $this->getViewAsConstantsForRoles($requiredRoles)
        );

        return $hasViewAsPermission;
    }

    /**
     * Check if user has any HQ role
     */
    public function hasAnyHqRole(?array $roles = null): bool
    {
        $rolesToCheck = $roles ?? DepartmentRoleRegistry::getRelationNames();

        return $this->hqRoleChecker(
            $rolesToCheck,
            $this->departmentName ?? 'Electric'
        );
    }

    /**
     * Check if user has any branch role
     */
    public function hasAnyBranchRole(int $branchId, ?array $roles = null): bool
    {
        $rolesToCheck = $roles ?? DepartmentRoleRegistry::getRelationNames();

        return $this->branchRoleChecker(
            $branchId,
            $rolesToCheck,
            $this->departmentName ?? 'Electric'
        );
    }

    /**
     * Get ViewAs constants for given roles
     */
    protected function getViewAsConstantsForRoles(?array $roles = null): array
    {
        if ($roles === null) {
            $roles = DepartmentRoleRegistry::all();
        }

        $constants = [];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $role = DepartmentRoleRegistry::get($role);
            }

            if ($role && isset($role['view_as_constant_prefix'])) {
                $constants[] = $role['view_as_constant_prefix'];
            }
        }

        return $constants;
    }
}

