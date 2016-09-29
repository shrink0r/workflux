<?php

namespace Workflux;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\State\StateInterface;
use Workflux\State\StateMap;
use Workflux\Transition\StateTransitions;
use Workflux\Transition\TransitionSet;

interface StateMachineInterface
{
    /**
     * @param InputInterface $input
     * @param string $start_state
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input, string $start_state): OutputInterface;

    /**
     * @return StateInterface
     */
    public function getInitialState(): StateInterface;

    /**
     * @return StateMap
     */
    public function getFinalStates(): StateMap;

    /**
     * @param string $state_name
     *
     * @return StateInterface
     */
    public function getState(string $state_name): StateInterface;

    /**
     * @return StateMap
     */
    public function getStates(): StateMap;

    /**
     * @param string $state_name
     *
     * @return TransitionSet
     */
    public function getStateTransitions(string $state_name): TransitionSet;

    /**
     * @return StateTransitions
     */
    public function getTransitions(): StateTransitions;
}
