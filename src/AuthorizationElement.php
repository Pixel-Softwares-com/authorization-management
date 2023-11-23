<?php

namespace AuthorizationManagement;

use  AuthorizationManagement\PermissionExaminers\PermissionExaminer;

abstract class AuthorizationElement
{
    protected PermissionExaminer $permissionExaminer;

    protected function initPermissionExaminer() : void
    {
        $this->permissionExaminer = PermissionExaminer::create();
    }

    public function __construct()
    {
        $this->initPermissionExaminer();
    }

}
