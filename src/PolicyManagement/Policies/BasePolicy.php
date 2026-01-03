<?php

namespace AuthorizationManagement\PolicyManagement\Policies;

use AuthorizationManagement\AuthorizationElement;
use AuthorizationManagement\AuthorizationModelManager;
use AuthorizationManagement\PermissionExaminers\PermissionExaminer;
use AuthorizationManagement\DepartmentRoles\DepartmentRoleRegistry;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Policy Abstract Class
 * 
 * Provides common authorization logic for resource models.
 * Extend this class to create specific policies for your models.
 * 
 * Includes all department role checking functionality inherited from AuthorizationElement.
 */
abstract class BasePolicy extends AuthorizationElement
{
    use HandlesAuthorization;

    // ================== Static Check Method ==================

    /**
     * Static method to check a policy action
     * 
     * @param string $policyAction
     * @param string|null $modelClass
     * @return bool
     * @throws Exception
     */
    public static function check(string $policyAction, ?string $modelClass = null): bool
    {
        if (!request()->user()) {
            throw PermissionExaminer::getUnAuthenticatingException();
        }
        return request()->user()->can($policyAction, $modelClass);
    }

    // ================== CRUD Policy Methods ==================

    /**
     * Determine whether the user can view any models.
     */
    // public function viewAny(Authenticatable $user): bool
    // {
    //     return true;
    // }

    /**
     * Determine whether the user can view the model.
     */
    // public function view(Authenticatable $user, Model $model): bool
    // {
    //     return true;
    // }

    /**
     * Determine whether the user can create models.
     */
    // public function create(Authenticatable $user): bool
    // {
    //     if ($this->isSuperAdmin($user)) {
    //         return true;
    //     }

    //     return $this->customCreateAuthorization($user);
    // }

    /**
     * Determine whether the user can update the model.
     */
    // public function update(Authenticatable $user, Model $model): bool
    // {
    //     if ($this->isSuperAdmin($user)) {
    //         return true;
    //     }

    //     if ($this->isCreator($user, $model)) {
    //         return true;
    //     }

    //     if ($this->hasManagerOrEngineerRoleForModel($user, $model)) {
    //         return true;
    //     }

    //     return $this->customEditAuthorization($user, $model);
    // }

    /**
     * Determine whether the user can delete the model.
     */
    // public function delete(Authenticatable $user, Model $model): bool
    // {
    //     if ($this->isSuperAdmin($user)) {
    //         return true;
    //     }

    //     if ($this->isCreator($user, $model)) {
    //         return true;
    //     }

    //     if ($this->hasManagerOrEngineerRoleForModel($user, $model)) {
    //         return true;
    //     }

    //     return $this->customDeleteAuthorization($user, $model);
    // }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(Authenticatable $user, Model $model): bool
    // {
    //     return $this->isSuperAdmin($user);
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(Authenticatable $user, Model $model): bool
    // {
    //     return $this->isSuperAdmin($user);
    // }

    // ================== Role Checking Methods ==================

    /**
     * Check if user has manager or engineer role for the model's branch/HQ
     * Uses default roles only (manager, engineer)
     * 
     * @param Authenticatable $user
     * @param Model $model
     * @return bool
     */
    protected function hasManagerOrEngineerRoleForModel(Authenticatable $user, Model $model): bool
    {
        $branchId = $model->branch_id ?? null;

        if (!$branchId) {
            return false;
        }

        $mainBranchId = AuthorizationModelManager::getMainBranchId();
        
        // Get role relations dynamically from registry (default roles only)
        $roleRelations = DepartmentRoleRegistry::getManagerAndEngineerRelations();

        if ($branchId === $mainBranchId) {
            return $this->departmentPermissionResolver
                ->forUser($user->id)
                ->forDepartment($this->getDefaultDepartmentName($model))
                ->hasAnyHqRole($roleRelations);
        }

        return $this->departmentPermissionResolver
            ->forUser($user->id)
            ->forDepartment($this->getDefaultDepartmentName($model))
            ->hasAnyBranchRole($branchId, $roleRelations);
    }

    /**
     * Check if user has manager role specifically
     * Uses manager role only from default roles
     * 
     * @param Authenticatable $user
     * @param Model $model
     * @return bool
     */
    protected function hasManagerRoleForModel(Authenticatable $user, Model $model): bool
    {
        $branchId = $model->branch_id ?? null;

        if (!$branchId) {
            return false;
        }

        $mainBranchId = AuthorizationModelManager::getMainBranchId();
        
        // Get manager relation dynamically from registry
        $managerRelation = DepartmentRoleRegistry::getManagerRelation();
        if (!$managerRelation) {
            return false;
        }

        if ($branchId === $mainBranchId) {
            return $this->departmentPermissionResolver
                ->forUser($user->id)
                ->forDepartment($this->getDefaultDepartmentName($model))
                ->hasAnyHqRole([$managerRelation]);
        }

        return $this->departmentPermissionResolver
            ->forUser($user->id)
            ->forDepartment($this->getDefaultDepartmentName($model))
            ->hasAnyBranchRole($branchId, [$managerRelation]);
    }

    /**
     * Check if user has any of the specified roles for the model
     * Merges custom roles with all default roles from configuration
     * 
     * @param Authenticatable $user
     * @param Model $model
     * @param array $customRoles Custom role relations to merge with defaults e.g., ['supervisors', 'team_leads']
     * @return bool
     */
    protected function hasAnyRoleForModel(Authenticatable $user, Model $model, array $customRoles = []): bool
    {
        $branchId = $model->branch_id ?? null;

        if (!$branchId) {
            return false;
        }

        $mainBranchId = AuthorizationModelManager::getMainBranchId();
        
        // Merge custom roles with all default roles from configuration
        $roleRelations = DepartmentRoleRegistry::mergeCustomRolesWithDefaults($customRoles);

        if ($branchId === $mainBranchId) {
            return $this->departmentPermissionResolver
                ->forUser($user->id)
                ->forDepartment($this->getDefaultDepartmentName($model))
                ->hasAnyHqRole($roleRelations);
        }

        return $this->departmentPermissionResolver
            ->forUser($user->id)
            ->forDepartment($this->getDefaultDepartmentName($model))
            ->hasAnyBranchRole($branchId, $roleRelations);
    }

    /**
     * Check ViewAs permissions for the model
     * Uses all default roles from configuration
     * 
     * @param Authenticatable $user
     * @param Model $model
     * @return bool
     */
    protected function hasViewAsPermission(Authenticatable $user, Model $model): bool
    {
        $viewAs = request()->input('view_as');

        if (!$viewAs) {
            return true;
        }

        // Get default role relations dynamically
        $roles = DepartmentRoleRegistry::getAllDefaultRoleRelations();

        $resolver = $this->departmentPermissionResolver
            ->forUser($user->id)
            ->forDepartment($this->getDefaultDepartmentName($model));

        if (in_array($viewAs, ['private', 'draft'])) {
            if ($resolver->hasAnyHqRole($roles)) {
                return true;
            }

            if (isset($model->branch_id)) {
                return $resolver->hasAnyBranchRole($model->branch_id, $roles);
            }
        }

        return $this->checkViewAsPermissions([$viewAs]);
    }

    // ================== Helper Methods ==================

    /**
     * Check if user is a super admin
     */
    protected function isSuperAdmin(Authenticatable $user): bool
    {
        return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
    }

    /**
     * Check if user is the creator of the model
     */
    protected function isCreator(Authenticatable $user, Model $model): bool
    {
        if (method_exists($model, 'isCreator')) {
            return $model->isCreator();
        }
        
        $creatorId = $model->creator_id ?? null;
        return $creatorId !== null && (int)$creatorId === (int)$user->id;
    }

    /**
     * Check if user is an owner representative
     */
    protected function isOwnerRep(Model $model): bool
    {
        return method_exists($model, 'isOwnerRep') && $model->isOwnerRep();
    }

    /**
     * Check if user is a responsible department representative
     */
    protected function isResponsibleDepartmentRep(Model $model): bool
    {
        return method_exists($model, 'isResponsibleDepartmentRep') && $model->isResponsibleDepartmentRep();
    }

    // ================== Customizable Methods (Override in Child) ==================

    /**
     * Get department name for the model
     * Override in child policies to specify the department
     * 
     * @param Model $model
     * @return string
     */
    protected function getDefaultDepartmentName(Model $model): string
    {
        return DepartmentRoleRegistry::getDefaultDepartmentName();
    }

    /**
     * Custom create authorization logic
     * Override in child policies for custom logic
     */
    protected function customCreateAuthorization(Authenticatable $user): bool
    {
        return false;
    }

    /**
     * Custom edit authorization logic
     * Override in child policies for custom logic
     */
    protected function customEditAuthorization(Authenticatable $user, Model $model): bool
    {
        return false;
    }

    /**
     * Custom delete authorization logic
     * Override in child policies for custom logic
     */
    protected function customDeleteAuthorization(Authenticatable $user, Model $model): bool
    {
        return false;
    }

    /**
     * Authorize or fail helper
     */
    protected function authorizeOrFail(bool $authorized, string $message = 'Unauthorized action'): bool
    {
        if (!$authorized) {
            abort(406, $message);
        }
        return true;
    }

    // ================== Department Role Registry Helpers ==================

    /**
     * Get all registered department role relation names
     */
    protected function getAllDepartmentRoleRelations(): array
    {
        return DepartmentRoleRegistry::getRelationNames();
    }

    /**
     * Check if a role key is valid
     */
    protected function isValidDepartmentRole(string $roleKey): bool
    {
        return DepartmentRoleRegistry::isValidRole($roleKey);
    }

    /**
     * Get specific role relation dynamically
     * 
     * @param string $roleKey The role key (e.g., 'manager', 'engineer', 'rep')
     * @return string|null
     */
    protected function getRoleRelation(string $roleKey): ?string
    {
        return DepartmentRoleRegistry::getRelationName($roleKey);
    }

    /**
     * Get manager relation dynamically
     */
    protected function getManagerRelation(): ?string
    {
        return DepartmentRoleRegistry::getManagerRelation();
    }

    /**
     * Get engineer relation dynamically
     */
    protected function getEngineerRelation(): ?string
    {
        return DepartmentRoleRegistry::getEngineerRelation();
    }

    /**
     * Get rep relation dynamically
     */
    protected function getRepRelation(): ?string
    {
        return DepartmentRoleRegistry::getRepRelation();
    }

    /**
     * Get all default role relations (manager, engineer, rep) dynamically
     * Returns default roles only
     * 
     * @return array
     */
    protected function getAllDefaultRoles(): array
    {
        return DepartmentRoleRegistry::getAllDefaultRoleRelations();
    }

    /**
     * Get manager and engineer relations dynamically
     * Returns default roles only
     * 
     * @return array
     */
    protected function getManagerAndEngineerRoles(): array
    {
        return DepartmentRoleRegistry::getManagerAndEngineerRelations();
    }

    /**
     * Merge custom roles with all default role relations
     * Use this when you need to include custom roles with defaults
     * 
     * @param array $customRoles Custom role relations to merge with defaults
     * @return array
     */
    protected function mergeWithDefaultRoles(array $customRoles): array
    {
        return DepartmentRoleRegistry::mergeCustomRolesWithDefaults($customRoles);
    }
}
