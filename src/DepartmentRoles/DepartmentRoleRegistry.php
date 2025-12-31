<?php

namespace AuthorizationManagement\DepartmentRoles;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Department Role Registry
 * 
 * Centralized registry for managing Department Roles (not Application Roles).
 * 
 * Department Roles define positions within departments (manager, engineer, rep),
 * while Application Roles define system permissions (admin, user via role_id).
 */
class DepartmentRoleRegistry
{
    protected static ?self $instance = null;
    protected ?Collection $roles = null;
    protected array $config;

    protected function __construct()
    {
        $this->config = config('authorization-management-config.department_roles', []);
        $this->loadRoles();
    }

    /**
     * Get singleton instance
     */
    public static function instance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Load all department roles from configuration
     */
    protected function loadRoles(): void
    {
        $cacheKey = 'authorization_department_roles';
        $cacheTtl = $this->config['cache']['ttl'] ?? 3600;

        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            $this->roles = Cache::get($cacheKey);
            return;
        }

        $defaultRoles = collect($this->config['default_roles'] ?? []);
        $customRoles = collect($this->config['custom_roles'] ?? []);

        $this->roles = $defaultRoles->merge($customRoles)
            ->filter(fn($role) => $role['enabled'] ?? false)
            ->map(function ($role, $key) {
                return $this->normalizeRole($key, $role);
            });

        if ($this->isCacheEnabled()) {
            Cache::put($cacheKey, $this->roles, $cacheTtl);
        }
    }

    /**
     * Normalize role data structure
     */
    protected function normalizeRole(string $key, array $role): array
    {
        return [
            'key' => $key,
            'relation' => $role['relation'] ?? $key . 's',
            'dep_role_value' => $role['dep_role_value'] ?? $key,
            'view_as_constant_prefix' => $role['view_as_constant_prefix'] ?? strtoupper($key),
            'label' => $role['label'] ?? ucfirst($key),
            'enabled' => $role['enabled'] ?? true,
            'can_be_disabled' => $role['can_be_disabled'] ?? true,
            'hierarchy_level' => $this->getHierarchyLevel($key),
        ];
    }

    /**
     * Get hierarchy level for a role
     */
    protected function getHierarchyLevel(string $roleKey): int
    {
        return $this->config['hierarchy'][$roleKey] ?? 0;
    }

    /**
     * Check if caching is enabled
     */
    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    /**
     * Get all enabled department roles
     */
    public static function all(): Collection
    {
        return static::instance()->roles;
    }

    /**
     * Get a specific role by key
     */
    public static function get(string $key): ?array
    {
        return static::instance()->roles->get($key);
    }

    /**
     * Get role by relation name
     */
    public static function getByRelation(string $relationName): ?array
    {
        return static::instance()->roles->first(
            fn($role) => $role['relation'] === $relationName
        );
    }

    /**
     * Get role by dep_role_value
     */
    public static function getByDepRoleValue(string $depRoleValue): ?array
    {
        return static::instance()->roles->first(
            fn($role) => $role['dep_role_value'] === $depRoleValue
        );
    }

    /**
     * Check if a dep_role value is valid
     */
    public static function isValidDepRole(string $depRoleValue): bool
    {
        return static::instance()->roles->contains(
            fn($role) => $role['dep_role_value'] === $depRoleValue
        );
    }

    /**
     * Check if a role key is valid
     */
    public static function isValidRole(string $key): bool
    {
        return static::instance()->roles->has($key);
    }

    /**
     * Get all dep_role values for validation
     */
    public static function getDepRoleValues(): array
    {
        return static::instance()->roles
            ->pluck('dep_role_value')
            ->values()
            ->toArray();
    }

    /**
     * Alias for getDepRoleValues - for backward compatibility
     */
    public static function getAllRoleValues(): array
    {
        return static::getDepRoleValues();
    }

    /**
     * Get dep_role value for a specific role key
     */
    public static function getRoleValue(string $key): ?string
    {
        $role = static::get($key);
        return $role['dep_role_value'] ?? null;
    }

    /**
     * Get all relation names
     */
    public static function getRelationNames(): array
    {
        return static::instance()->roles
            ->pluck('relation')
            ->values()
            ->toArray();
    }

    /**
     * Get validation rules for dep_role field
     */
    public static function getValidationRules(): array
    {
        $validation = config('authorization-management-config.department_roles.validation', []);
        
        $baseRules = [
            'string',
            'max:' . ($validation['max_length'] ?? 50),
        ];
        
        if ($validation['nullable'] ?? true) {
            $baseRules[] = 'nullable';
        }

        $validValues = static::getDepRoleValues();
        
        if (!empty($validValues)) {
            $baseRules[] = 'in:' . implode(',', $validValues);
        }

        return $baseRules;
    }

    /**
     * Get default roles only
     */
    public static function getDefaultRoles(): Collection
    {
        $defaultKeys = array_keys(config('authorization-management-config.department_roles.default_roles', []));
        
        return static::instance()->roles->filter(
            fn($role, $key) => in_array($key, $defaultKeys)
        );
    }

    /**
     * Get custom roles only
     */
    public static function getCustomRoles(): Collection
    {
        $customKeys = array_keys(config('authorization-management-config.department_roles.custom_roles', []));
        
        return static::instance()->roles->filter(
            fn($role, $key) => in_array($key, $customKeys)
        );
    }

    /**
     * Get roles for a specific department
     */
    public static function getForDepartment(string $departmentName): Collection
    {
        $departmentRoles = config("authorization-management-config.department_roles.department_specific_roles.{$departmentName}");
        
        if (!$departmentRoles) {
            return static::all();
        }

        return static::instance()->roles->filter(
            fn($role, $key) => in_array($key, $departmentRoles)
        );
    }

    /**
     * Check if one role is higher than another in hierarchy
     */
    public static function isHigherThan(string $role1Key, string $role2Key): bool
    {
        $role1 = static::get($role1Key);
        $role2 = static::get($role2Key);

        if (!$role1 || !$role2) {
            return false;
        }

        return $role1['hierarchy_level'] > $role2['hierarchy_level'];
    }

    /**
     * Clear the roles cache
     */
    public static function clearCache(): void
    {
        Cache::forget('authorization_department_roles');
        static::$instance = null;
    }

    /**
     * Reload roles from configuration
     */
    public static function reload(): void
    {
        static::clearCache();
        static::instance();
    }
}

