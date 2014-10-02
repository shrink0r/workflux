<?php

namespace Workflux\Transition;

interface ITransition
{
    public function getName();

    public function getIncomingStateNames();

    public function getOutgoingStateName();

    public function getGuard();

    public function hasGuard();
}
