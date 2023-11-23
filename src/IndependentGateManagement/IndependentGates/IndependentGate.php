<?php

namespace AuthorizationManagement\IndependentGateManagement\IndependentGates;

use AuthorizationManagement\AuthorizationElement;

abstract class IndependentGate extends  AuthorizationElement
{
    abstract public function define() : void;
}
