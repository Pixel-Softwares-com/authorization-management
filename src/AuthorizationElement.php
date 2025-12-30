<?php

namespace AuthorizationManagement;

use AuthorizationManagement\PermissionExaminers\PermissionExaminer;
use AuthorizationManagement\DepartmentRoles\BranchDepartmentPermissionResolver;
use AuthorizationManagement\DepartmentRoles\Traits\HqRoleChecker;
use AuthorizationManagement\DepartmentRoles\Traits\BranchRoleChecker;
use AuthorizationManagement\DepartmentRoles\Traits\CheckViewAsPermissions;

/**
 * Authorization Element Abstract Class
 * 
 * Base class for all authorization elements (Policies, Gates).
 * Includes department role checking traits that are available to all child classes.
 */
abstract class AuthorizationElement
{
    // Department Role Checking Traits
    use HqRoleChecker;
    use BranchRoleChecker;
    use CheckViewAsPermissions;

    protected PermissionExaminer $permissionExaminer;
    protected BranchDepartmentPermissionResolver $departmentPermissionResolver;

    /**
     * Initialize the Permission Examiner
     */
    protected function initPermissionExaminer(): void
    {
        $this->permissionExaminer = PermissionExaminer::create();
    }

    /**
     * Initialize the Department Permission Resolver
     */
    protected function initDepartmentPermissionResolver(): void
    {
        $this->departmentPermissionResolver = BranchDepartmentPermissionResolver::make();
    }

    public function __construct()
    {
        $this->initPermissionExaminer();
        $this->initDepartmentPermissionResolver();
    }

    /**
     * Get the Permission Examiner instance
     */
    protected function getPermissionExaminer(): PermissionExaminer
    {
        return $this->permissionExaminer;
    }

    /**
     * Get the Department Permission Resolver instance
     */
    protected function getDepartmentPermissionResolver(): BranchDepartmentPermissionResolver
    {
        return $this->departmentPermissionResolver;
    }

    /**
     * Create a new Department Permission Resolver with optional user ID
     */
    protected function createDepartmentResolver(?int $userId = null): BranchDepartmentPermissionResolver
    {
        return BranchDepartmentPermissionResolver::make($userId);
    }
}
