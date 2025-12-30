<?php

namespace AuthorizationManagement\DepartmentRoles\Traits;

use AuthorizationManagement\AuthorizationModelManager;

/**
 * HQ Role Checker Trait
 * 
 * Provides functionality to check if the current user has specific roles
 * in the Headquarters (main branch) department structure.
 */
trait HqRoleChecker
{
    /**
     * Check if the authenticated user has any of the specified roles
     * in the HQ (main branch) for a specific department.
     * 
     * @param array $relations Array of relation names to check (e.g., ['managers', 'engineers'])
     * @param string $departmentName The name of the department to check within
     * @return bool
     */
    public function hqRoleChecker(array $relations, string $departmentName = 'HSE'): bool
    {
        $mainBranch = $this->getMainBranchForRoleCheck();

        if (!$mainBranch) {
            return false;
        }

        foreach ($relations as $relation) {
            if ($mainBranch->departments()
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
     * Get the main branch (HQ) for role checking
     * 
     * @return mixed|null
     */
    private function getMainBranchForRoleCheck()
    {
        try {
            return AuthorizationModelManager::getMainBranch();
        } catch (\RuntimeException $e) {
            // Model not configured - return null to fail gracefully
            return null;
        }
    }
}

