<?php

namespace Workflux\Transition;

use Workflux\Guard\IGuard;

class Transition implements ITransition
{
    protected $name;

    protected $incoming_state_names;

    protected $outgoing_state_name;

    protected $guard;

    public function __construct($name, array $incoming_state_names, $outgoing_state_name, IGuard $guard = null)
    {
        $this->name = $name;
        $this->incoming_state_names = $incoming_state_names;
        $this->outgoing_state_name = $outgoing_state_name;
        $this->guard = $guard;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIncomingStateNames()
    {
        return $this->incoming_state_names;
    }

    public function getOutgoingStateName()
    {
        return $this->outgoing_state_name;
    }

    public function getGuard()
    {
        return $this->guard;
    }

    public function hasGuard()
    {
        return null !== $this->guard;
    }
}
