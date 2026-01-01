<?php

namespace AuthorizationManagement\PermissionExaminers;


use AuthorizationManagement\Helpers\Helpers;
use AuthorizationManagement\Interfaces\HasAuthorizablePermissions;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

class PermissionExaminer
{
    protected Authenticatable | HasAuthorizablePermissions $loggedUser ;
    protected array $userPermissions = [];
    protected array $permissionsToControl = [];

    protected static ?string $unauthorizedAccessExceptionClass = null;
    static protected string $denyMessage = "You don't have the permission for browsing this page !";
    static protected int $denyStatusCode = 406;

    static public function create() : PermissionExaminer
    {
        return new static();
    }

    public function addPermissionToCheck(string $permission) : PermissionExaminer
    {
        $this->permissionsToControl[] = $permission;
        return $this;
    }

    public function addPermissionsToCheck(array $permissions) : PermissionExaminer
    {
        foreach ($permissions as $permission)
        {
            $this->addPermissionToCheck($permission);
        }
        return $this;
    }

    static public function setUnauthorizedAccessExceptionClass(string $exceptionClass) : void
    {
        if(is_subclass_of($exceptionClass , Exception::class))
        {
            static::$unauthorizedAccessExceptionClass = $exceptionClass;
        }
    }

    static public function getUnauthorizedAccessExceptionClass()  : ?string
    {
        return static::$unauthorizedAccessExceptionClass;
    }

    /**
     * @param int $denyStatusCode
     * @return void
     * is used with default exception only 
     * not used with custom UnauthorizedAccessException Class
     */
    static public function setDenyStatusCode(int $denyStatusCode): void
    {
        static::$denyStatusCode = $denyStatusCode;
    }

    /**
     * @param string $denyMessage
     * 
     * is used with default exception only 
     * not used with custom UnauthorizedAccessException Class
     */
    static public function setDenyMessage(string $denyMessage): void
    {
        static::$denyMessage = $denyMessage;
    }

    /**
     * @return int
     */
    static public function getDenyStatusCode(): int
    {
        return self::$denyStatusCode;
    }

    /**
     * @return string
     */
    static public function getDenyMessage(): string
    {
        return static::$denyMessage;
    }

    static public function getUnauthorizedAccessException() : Exception
    {
        if($exceptionClass = static::getUnauthorizedAccessExceptionClass())
        {
            return new $exceptionClass();
        }

        //the default exception used on no custom exception is set
        return new Exception(static::getDenyMessage() , static::getDenyStatusCode());
    }

    /**
     * @deprecated
     * will be removed later
     */
    static public function getUnAuthenticatingException() : Exception
    {
        return static::getUnauthorizedAccessException();
    }

    protected function setUserPermissions() : void
    {
        $this->userPermissions = $this->loggedUser->permissions() ;
    }

    protected function checkUserAuthorizationConditions(Authenticatable $user) : void
    {
        if(!$user instanceof HasAuthorizablePermissions)
        {
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass("The logged user doesn't implement HasAuthorizablePermissions interface ... It is Unable to be authorized " , static::getDenyStatusCode());
        }
    }

    protected function checkUserType($user = null) : void
    {
        if(!$user)
        {
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass("There is no logged user to be authorized");
        }

        if(!$user instanceof Authenticatable)
        {
            $exceptionClass = Helpers::getExceptionClass();
            throw new $exceptionClass("The logged user doesn't inherit Authenticatable class .. It is Unable to be authorized");
        }
    }
    protected function setValidLoggedUser() : void
    {
        $user = auth()->user();

        $this->checkUserType($user);

        /** If No exception is thrown ... the logged user is an Authenticatable object */
        $this->checkUserAuthorizationConditions($user);

        /** If No exception is thrown ... the logged user is an Authenticatable object and has permissions array */
        $this->loggedUser = $user;
    }

    protected function setUserProps() : void
    {
        $this->setValidLoggedUser();
        $this->setUserPermissions();
    }

    /**
     * @return bool
     * This is useful when you need to allow to user if he has a permission OR he makes another condition is true
     * So you can do this by checking the permission and retrieving a boolean typed value only
     */
    public function hasPermissions() : bool
    {
        $this->setUserProps();
        //array_intersect: check only values for arrays
        $intersectedPermissions = array_intersect($this->userPermissions , $this->permissionsToControl);
        if (count($this->userPermissions) > 0 && count($intersectedPermissions) == count($this->permissionsToControl))
        {
            return true;
        }
        return false;

    }

    /**
     * @return bool
     * @throws Exception
     */
    public function hasPermissionsOrFail() : bool
    {
        if($this->hasPermissions())
        {
            return true;
        }
        /**
         * false value will never be returned .... true or an exception will be thrown
         */
        throw $this::getUnAuthenticatingException();
    }

}
