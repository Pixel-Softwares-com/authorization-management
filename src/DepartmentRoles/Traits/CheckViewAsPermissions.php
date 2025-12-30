<?php

namespace AuthorizationManagement\DepartmentRoles\Traits;

/**
 * Check ViewAs Permissions Trait
 * 
 * Provides functionality to check if a request's view_as parameter
 * matches allowed options.
 */
trait CheckViewAsPermissions
{
    /**
     * Check if the current request's view_as parameter matches any of the allowed options.
     * 
     * @param array $defaultOptions Array of allowed view_as values
     * @return bool
     */
    public function checkViewAsPermissions(array $defaultOptions = []): bool
    {
        $viewAs = request()->query('view_as') ?? request()->input('view_as');
        return in_array($viewAs, $defaultOptions, true);
    }
}

