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
    public function execute(InputInterface $input, $start_state);

    /**
     * @return StateInterface
     */
    public function getInitialState();

    /**
     * @return StateInterface[]
     */
    public function getStates();

    /**
     * @return TransitionInterface[]
     */
    public function getTransitions();
}
