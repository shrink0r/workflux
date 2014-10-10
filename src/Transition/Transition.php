<?php

namespace Workflux\Transition;

use Workflux\Guard\GuardInterface;

class Transition implements TransitionInterface
{
    /**
     * @var array $incoming_state_names
     */
    protected $incoming_state_names;

    /**
     * @var string $outgoing_state_name
     */
    protected $outgoing_state_name;

    /**
     * @var GuardInterface $guard
     */
    protected $guard;

    /**
     * Creates a new Transition instance.
     *
     * @param mixed $incoming_state_name_or_names
     * @param mixed $outgoing_state_name
     * @param GuardInterface $guard
     */
    public function __construct($incoming_state_name_or_names, $outgoing_state_name, GuardInterface $guard = null)
    {
        $this->incoming_state_names = (array)$incoming_state_name_or_names;
        $this->outgoing_state_name = $outgoing_state_name;
        $this->guard = $guard;
    }

    /**
     * Returns the names of the transition's incoming states.
     *
     * @return array
     */
    public function getIncomingStateNames()
    {
        return $this->incoming_state_names;
    }

    /**
     * Returns the name of the transition's outgoing state.
     *
     * @return array
     */
    public function getOutgoingStateName()
    {
        return $this->outgoing_state_name;
    }

    /**
     * Returns the transition's guard.
     *
     * @return GuardInterface
     */
    public function getGuard()
    {
        return $this->guard;
    }

    /**
     * Tells whether the transition has a guard or not.
     *
     * @return bool
     */
    public function hasGuard()
    {
        return null !== $this->guard;
    }
}
