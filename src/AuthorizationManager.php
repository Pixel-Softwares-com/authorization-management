<?php

namespace AuthorizationManagement;

abstract class AuthorizationManager
{

    static protected array $instances = [];
    protected AuthorizationElementContainer $authorizationElementContainer;

    abstract public function defineAll() : void;
    abstract protected function getAuthorizationElementContainer()  :AuthorizationElementContainer;

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

    protected function initAuthorizationElementContainer() : void
    {
        $this->authorizationElementContainer = $this->getAuthorizationElementContainer() ;
    }
    private function __construct()
    {
        $this->initAuthorizationElementContainer();
    }

}
