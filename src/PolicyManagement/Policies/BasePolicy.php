<?php

namespace AuthorizationManagement\PolicyManagement\Policies;;

use AuthorizationManagement\AuthorizationElement;
use AuthorizationManagement\PermissionExaminers\PermissionExaminer;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy extends AuthorizationElement
{
    use HandlesAuthorization;

    /**
     * @param string $policyAction
     * @param string|null $modelClass
     * @return bool
     * @throws Exception
     */
    public static function check(string $policyAction , ?string $modelClass = null ) : bool
    {
        if(!request()->user())
        {
            throw PermissionExaminer::getUnAuthenticatingException();
        }
        /**
         * The policy calling will return its return value what ever it is
         * But You should use <<<>>>>
         */
        return request()->user()->can($policyAction , $modelClass);
    }

}
