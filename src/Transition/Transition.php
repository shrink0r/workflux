<?php

namespace Workflux\Transition;

use Workflux\Guard\GuardInterface;

class Transition implements TransitionInterface
{
    protected $incoming_state_names;

    protected $outgoing_state_name;

    protected $guard;

    public function __construct($incoming_state_name_or_names, $outgoing_state_name, GuardInterface $guard = null)
    {
        $this->incoming_state_names = (array)$incoming_state_name_or_names;
        $this->outgoing_state_name = $outgoing_state_name;
        $this->guard = $guard;
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
