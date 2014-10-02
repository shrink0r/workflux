<?php

namespace Workflux\StateMachine;

use Workflux\IStatefulSubject;

interface IStateMachine
{
    public function getName();

    public function execute(IStatefulSubject $subject, $transition_name);

    public function getCurrentStateFor(IStatefulSubject $subject);

    public function getStates();

    public function getState($state_name);

    public function getTransitions($state_name = null);

    public function getTransition($state_name, $transition_name);
}
