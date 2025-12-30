<?php

namespace AuthorizationManagement\DepartmentRoles\Traits;

use AuthorizationManagement\AuthorizationModelManager;

/**
 * Branch Role Checker Trait
 * 
 * Provides functionality to check if the current user has specific roles
 * in a specific branch's department structure.
 */
trait BranchRoleChecker
{
    /**
     * Check if the authenticated user has any of the specified roles
     * in a specific branch for a specific department.
     * 
     * @param int|null $branchId The ID of the branch to check
     * @param array $relations Array of relation names to check (e.g., ['managers', 'engineers'])
     * @param string $departmentName The name of the department to check within
     * @return bool
     */
    public function branchRoleChecker(?int $branchId, array $relations, string $departmentName = 'HSE'): bool
    {
        $branch = $this->getBranchByIdForRoleCheck($branchId);

        if (!$branch) {
            return false;
        }

        foreach ($relations as $relation) {
            if ($branch->departments()
                ->where('name', $departmentName)
                ->whereHas($relation, fn($q) => $q->where('id', auth()->id()))
                ->exists()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a branch by ID for role checking
     * 
     * @param int|null $branchId
     * @return mixed|null
     */
    private function getBranchByIdForRoleCheck(?int $branchId)
    {
        if (!$branchId) {
            return null;
        }

        try {
            return AuthorizationModelManager::getBranchById($branchId);
        } catch (\RuntimeException $e) {
            // Model not configured - return null to fail gracefully
            return null;
        }
    }
}

