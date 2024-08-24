<?php

namespace AuthorizationManagement\PolicyManagement\PolicyManagers;

use AuthorizationManagement\AuthorizationElementContainer;
use AuthorizationManagement\AuthorizationManager;
use AuthorizationManagement\PolicyManagement\PolicyContainers\PolicyContainer;
use Illuminate\Support\Facades\Gate;

class PolicyManager extends AuthorizationManager
{
    protected function getAuthorizationElementContainer()  :AuthorizationElementContainer
    {
        return PolicyContainer::Singleton();
    }

    public function defineAll() : void
    {
        foreach ($this->authorizationElementContainer->getAuthorizationElements() as  $modelClass => $policyClass)
        {
            Gate::policy($modelClass , $policyClass);
        }
    }
}
