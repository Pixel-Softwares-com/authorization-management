<?php

namespace AuthorizationManagement\DepartmentRoles\Traits;

use AuthorizationManagement\AuthorizationModelManager;
use AuthorizationManagement\DepartmentRoles\DepartmentRoleRegistry;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Has Dynamic Department Roles Trait
 * 
 * This trait dynamically creates Eloquent relationships for department roles
 * based on the configuration in authorization-management config.
 * 
 * The relationship is HasMany (not BelongsToMany):
 * - Users have `department_id` and `dep_role` columns
 * - managers() returns users where department_id = this.id AND dep_role = 'manager'
 * - teamMembers() returns users where department_id = this.id AND dep_role IS NOT NULL
 * 
 * Apply this trait to your Department model.
 */
trait HasDynamicDepartmentRoles
{
    /**
     * Boot the trait - register dynamic accessors
     */
    public static function bootHasDynamicDepartmentRoles(): void
    {
        // Register appends dynamically if needed
    }

    /**
     * Handle dynamic calls to undefined methods.
     * Creates relationships on-the-fly based on registered department roles.
     * 
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // Check if this is a department role relation (e.g., managers, engineers, reps)
        $role = DepartmentRoleRegistry::getByRelation($method);
        
        if ($role) {
            return $this->createDepartmentRoleRelation($role);
        }

        // Fall back to parent's __call
        return parent::__call($method, $parameters);
    }

    /**
     * Handle dynamic attribute access for _ids attributes
     * This properly handles Laravel's appends (e.g., managers_ids, reps_ids)
     * 
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        // Check if this is asking for IDs (e.g., managers_ids, reps_ids)
        if (str_ends_with($key, '_ids')) {
            $relationName = substr($key, 0, -4); // Remove '_ids' suffix
            $role = DepartmentRoleRegistry::getByRelation($relationName);
            
            if ($role) {
                return $this->getDepartmentRoleIds($relationName);
            }
        }

        // Fall back to parent's getAttribute
        return parent::getAttribute($key);
    }

    /**
     * Get IDs for a department role relation
     * Uses eager loaded relation if available to prevent N+1
     * 
     * @param string $relationName
     * @return array
     */
    protected function getDepartmentRoleIds(string $relationName): array
    {
        // Use eager loaded relation if available
        if ($this->relationLoaded($relationName)) {
            return $this->{$relationName}?->pluck('id')->toArray() ?? [];
        }

        // Otherwise query directly
        return $this->{$relationName}()->pluck('id')->toArray();
    }

    /**
     * Create a department role relationship (HasMany)
     * 
     * Users have department_id and dep_role columns.
     * This returns users where department_id = this.id AND dep_role = role_value
     * 
     * @param array $role
     * @return HasMany
     */
    protected function createDepartmentRoleRelation(array $role): HasMany
    {
        $userModel = AuthorizationModelManager::getUserModel();
        $depRoleValue = $role['dep_role_value'];

        return $this->hasMany($userModel, 'department_id', 'id')
            ->where('dep_role', $depRoleValue);
    }

    /**
     * Get all team members (users with any department role)
     * 
     * Returns users where department_id = this.id AND dep_role IS NOT NULL
     * 
     * @return HasMany
     */
    public function teamMembers(): HasMany
    {
        $userModel = AuthorizationModelManager::getUserModel();

        return $this->hasMany($userModel, 'department_id', 'id')
            ->whereNotNull('dep_role');
    }

    /**
     * Get all users in this department (regardless of dep_role)
     * 
     * @return HasMany
     */
    public function allDepartmentUsers(): HasMany
    {
        $userModel = AuthorizationModelManager::getUserModel();

        return $this->hasMany($userModel, 'department_id', 'id');
    }

    /**
     * Scope: Filter departments that have a specific user as a team member
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasTeamMember($query, int $userId)
    {
        return $query->whereHas('teamMembers', function ($q) use ($userId) {
            $q->where('id', $userId);
        });
    }

    /**
     * Scope: Filter departments where user has specific role(s)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @param array $roleKeys Role keys (e.g., ['manager', 'engineer'])
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasUserWithRole($query, int $userId, array $roleKeys)
    {
        $depRoleValues = [];
        
        foreach ($roleKeys as $key) {
            $role = DepartmentRoleRegistry::get($key);
            if ($role) {
                $depRoleValues[] = $role['dep_role_value'];
            }
        }

        if (empty($depRoleValues)) {
            return $query;
        }

        $userModel = AuthorizationModelManager::getUserModel();

        return $query->whereHas('allDepartmentUsers', function ($q) use ($userId, $depRoleValues) {
            $q->where('id', $userId)
              ->whereIn('dep_role', $depRoleValues);
        });
    }

    /**
     * Scope: Eager load all department roles
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAllDepartmentRoles($query)
    {
        $relations = DepartmentRoleRegistry::getRelationNames();
        return $query->with($relations);
    }

    /**
     * Check if dynamic attribute exists (for Eloquent accessors)
     * 
     * @param string $key
     * @return bool
     */
    public function hasGetMutator($key): bool
    {
        // Check if this is a _ids attribute for a department role
        if (str_ends_with($key, '_ids')) {
            $relationName = substr($key, 0, -4);
            $role = DepartmentRoleRegistry::getByRelation($relationName);
            if ($role) {
                return true;
            }
        }

        return parent::hasGetMutator($key);
    }

    /**
     * Get list of all dynamic relations for this model
     * 
     * @return array
     */
    public function getDynamicDepartmentRelations(): array
    {
        return DepartmentRoleRegistry::getRelationNames();
    }

    /**
     * Get all appends that should be added for department roles
     * 
     * @return array
     */
    public function getDepartmentRoleAppends(): array
    {
        $appends = [];
        foreach (DepartmentRoleRegistry::all() as $role) {
            $appends[] = $role['relation'] . '_ids';
        }
        return $appends;
    }
}
