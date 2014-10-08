<?php

namespace Workflux\Builder;

use Workflux\State\StateInterface;
use Workflux\Transition\TransitionInterface;

interface StateMachineBuilderInterface
{
    public function setStateMachineName($state_machine_name);

    public function addState(StateInterface $state);

    public function addStates(array $states);

    public function addTransition(TransitionInterface $transition, $event_name = '');

    public function addTransitions(array $transitions);

    public function build();
}
