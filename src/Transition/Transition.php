<?php

namespace Workflux\Transition;

use Workflux\Guard\GuardInterface;

/**
 * Standard implementation of the TransitionInterface.
 */
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
     * @var GuardInterface|null $guard
     */
    protected $guard;

    /**
     * Creates a new Transition instance.
     *
     * @param mixed $incoming_state_name_or_names
     * @param string $outgoing_state_name
     * @param GuardInterface|null $guard
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
     * @return string
     */
    public function getOutgoingStateName()
    {
        return $this->outgoing_state_name;
    }

    /**
     * Returns the transition's guard.
     *
     * @return GuardInterface|null
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
