<?php


namespace AuthorizationManagement\IndependentGateManagement\IndependentGateContainer;

use AuthorizationManagement\AuthorizationElementContainer;
use AuthorizationManagement\IndependentGateManagement\IndependentGates\IndependentGate;

class IndependentGateContainer extends AuthorizationElementContainer
{
    protected function getIndependentGateClasses() : array
    {
        return config("authorization-management-config.independent_gates") ?? [] ;
    }

    protected  function addIndependentGateElement(string $gateClass ) : self
    {
        /**
         * Here we need to init the class to call its define method later to register the gate callback
         * while we don't need to init the policy or the related model because they are only initialized on need (when calling a policy action)
         */

        $gate  = new $gateClass();
        if($gate instanceof IndependentGate)
        {
            $this->authorizationElements[] = $gate;
        }
        return $this;
    }

    protected function initAuthorizationElements() : void
    {
        foreach ($this->getIndependentGateClasses() as  $gateClass)
        {
            $this->addIndependentGateElement($gateClass);
        }
    }
}
