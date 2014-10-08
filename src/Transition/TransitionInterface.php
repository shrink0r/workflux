<?php

namespace Workflux\Transition;

interface TransitionInterface
{
    public function getIncomingStateNames();

    public function getOutgoingStateName();

    public function getGuard();

    public function hasGuard();
}
