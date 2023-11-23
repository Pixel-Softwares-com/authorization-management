<?php

namespace AuthorizationManagement\PolicyManagement\PolicyContainers;

use AuthorizationManagement\AuthorizationElementContainer;

class PolicyContainer extends AuthorizationElementContainer
{
    protected function getPolicyClassMap() : array
    {
        return config("authorization-management-config.policies") ?? [] ;
    }

    protected  function addPolicyElement(string $modelClass , string $policyClass ) : self
    {
        if(class_exists($modelClass) && class_exists($policyClass))
        {
            /**
             * we don't need to init the policy or the related model because they are only initialized on need (when calling a policy action)
             */

            $this->authorizationElements[$modelClass] = $policyClass;
        }

        return $this;
    }

    protected function initAuthorizationElements() : void
    {
        foreach ($this->getPolicyClassMap() as $modelClass => $policyClass)
        {
            $this->addPolicyElement($modelClass , $policyClass  );
        }
    }
}
