<?php

namespace Workflux;

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
     * @return StateMap
     */
    public function getStates(): StateMap;

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
     * @param string $state_name
     *
     * @return TransitionSet
     */
    public function getStateTransitions(string $state_name): TransitionSet;

    /**
     * @return StateTransitionMap
     */
    public function getTransitions(): StateTransitionMap;
}
