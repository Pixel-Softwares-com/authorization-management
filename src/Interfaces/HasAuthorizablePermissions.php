<?php

namespace AuthorizationManagement\Interfaces;

interface HasAuthorizablePermissions
{
    public function permissions(): array;
}