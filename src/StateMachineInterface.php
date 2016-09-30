<?php

namespace Workflux;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\State\StateInterface;
use Workflux\State\StateMap;
use Workflux\Transition\StateTransitions;

interface StateMachineInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return StateInterface
     */
    public function getInitialState(): StateInterface;

    /**
     * @return StateMap
     */
    public function getFinalStates(): StateMap;

    /**
     * @return StateMap
     */
    public function getStates(): StateMap;

    /**
     * @param string $state_name
     *
     * @return StateTransitions
     */
    public function getStateTransitions(): StateTransitions;

    /**
     * @param InputInterface $input
     * @param string $start_state
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input, string $start_state): OutputInterface;
}
