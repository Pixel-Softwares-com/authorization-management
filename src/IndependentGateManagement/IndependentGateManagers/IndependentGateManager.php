<?php


namespace AuthorizationManagement\IndependentGateManagement\IndependentGateManagers;

use AuthorizationManagement\AuthorizationElementContainer;
use AuthorizationManagement\AuthorizationManager;
use AuthorizationManagement\independentGateManagement\IndependentGateContainer\IndependentGateContainer;
use AuthorizationManagement\independentGateManagement\IndependentGates\IndependentGate;

class IndependentGateManager extends AuthorizationManager
{
    protected function getAuthorizationElementContainer()  :AuthorizationElementContainer
    {
        return IndependentGateContainer::Singleton();
    }
    public function defineAll() : void
    {
        /**  @var IndependentGate $gateElement */
        foreach ($this->authorizationElementContainer->getAuthorizationElements() as  $gateElement)
        {
            $gateElement->define();
        }
    }
}
