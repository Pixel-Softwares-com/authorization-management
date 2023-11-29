<?php

namespace AuthorizationManagement\Helpers;

use Exception;

class Helpers
{
    static public function getExceptionClass() : string
    {
        $customExceptionClass = config("authorization-management-config.custom-exception-class");
        return is_subclass_of($customExceptionClass , Exception::class)
               ? $customExceptionClass
               : Exception::class;
    }

}