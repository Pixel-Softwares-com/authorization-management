<?php

namespace AuthorizationManagement;


abstract class AuthorizationElementContainer
{

    static protected array $instances = [];
    protected array $authorizationElements = [];

    abstract protected function initAuthorizationElements() : void;

    /**
     * Singleton methods - start
     */
    static protected function createInstance() : self
    {
        return new static();
    }

    static public function Singleton() : self
    {
        $currentClass = static::class;
        if (!array_key_exists($currentClass, static::$instances))
        {
            static::$instances[$currentClass] = static::createInstance();
        }
        return static::$instances[$currentClass];
    }
    /**
     * Singleton methods - end
     */

    /**
     * @return array
     */
    public function getAuthorizationElements(): array
    {
        $this->initAuthorizationElements();
        return $this->authorizationElements;
    }

}
