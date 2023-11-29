<?php

namespace AuthorizationManagement\PermissionExaminers;

use AuthorizationManagement\Exceptions\JsonException;
use AuthorizationManagement\Helpers\Helpers;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

class PermissionExaminer
{
    protected null |  Authenticatable $loggedUser  = null;
    protected array $userPermissions = [];
    protected array $permissionsToControl = [];
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

    /**
     * @param int $denyStatusCode
     * @return void
     */
    static public function setDenyStatusCode(int $denyStatusCode): void
    {
        static::$denyStatusCode = $denyStatusCode;
    }

    /**
     * @param string $denyMessage
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
    static public function getUnAuthenticatingException() : Exception
    {
        $exceptionClass = Helpers::getExceptionClass();
        return new $exceptionClass(static::getDenyMessage() , static::getDenyStatusCode());
    }

    protected function setUserProps() : void
    {
        $this->loggedUser = auth()->user();
        $this->userPermissions = $this->loggedUser?->permissions() ?? [];
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
