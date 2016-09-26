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
     * @return StateSet
     */
    public function getStates(): StateSet;

    /**
     * @return StateInterface
     */
    public function getInitialState(): StateInterface;

    /**
     * @return StateSet
     */
    public function getFinalStates(): StateSet;

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
     * @return TransitionSet
     */
    public function getTransitions(): TransitionSet;
}
