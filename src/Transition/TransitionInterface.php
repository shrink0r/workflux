<?php

namespace Workflux\Transition;

use Workflux\Guard\GuardInterface;

/**
 * TransitionInterface implementations model the connections between states.
 * They define the possible paths of traversal and can guard themselves from being used without permission,
 * by rejecting access for undesired subjects.
 */
interface TransitionInterface
{
    /**
     * Returns the names of the transition's incoming states.
     *
     * @return array
     */
    public function getIncomingStateNames();

    /**
     * Returns the name of the transition's outgoing state.
     *
     * @return array
     */
    public function getOutgoingStateName();

    /**
     * Returns the transition's guard.
     *
     * @return GuardInterface
     */
    public function getGuard();

    /**
     * Tells whether the transition has a guard or not.
     *
     * @return bool
     */
    public function hasGuard();
}
