<?php

namespace AuthorizationManagement\DepartmentRoles\Traits;

use AuthorizationManagement\DepartmentRoles\BranchDepartmentPermissionResolver;

/**
 * Filtered Branches Permissions Trait
 * 
 * Provides functionality to get permissions for filtered branches
 * based on the user's department roles.
 * 
 * @deprecated Use BranchDepartmentPermissionResolver service directly for new code.
 */
trait FilteredBranchesPermissions
{
    use HqRoleChecker, BranchRoleChecker;

    private static int $mainBranchId = 1;

    /**
     * Get filtered branches permissions
     * 
     * This method uses BranchDepartmentPermissionResolver which reads all role configurations
     * from the authorization-management config file.
     * 
     * @param int $userId
     * @param array $filteredBranchIds
     * @param array $customRoleSettings Format: ['relation_name' => ['enabled' => bool]]
     * @param bool $useDefaultSettings
     * @param string $roleCheckerDepartment
     * @return array
     * 
     * @deprecated Use BranchDepartmentPermissionResolver::make()->forDepartment($dept)->forBranches($branches)->resolve()
     */
    public function getFilteredBranchesPermissions(
        int $userId,
        array $filteredBranchIds = [],
        array $customRoleSettings = [],
        bool $useDefaultSettings = true,
        string $roleCheckerDepartment = 'Electric'
    ): array {
        $resolver = BranchDepartmentPermissionResolver::make($userId)
            ->forBranches($filteredBranchIds)
            ->forDepartment($roleCheckerDepartment);

        if (!empty($customRoleSettings)) {
            return $resolver->resolveWithCustomSettings(
                $customRoleSettings,
                $useDefaultSettings
            );
        }

        return $resolver->resolve();
    }
}

