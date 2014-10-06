<?php

namespace Workflux\Builder;

use Workflux\State\IState;
use Workflux\Transition\ITransition;

interface IStateMachineBuilder
{
    public function setStateMachineName($state_machine_name);

    public function addState(IState $state);

    public function addStates(array $states);

    public function addTransition(ITransition $transition, $event_name);

    public function addTransitions(array $transitions);

    public function build();
}
