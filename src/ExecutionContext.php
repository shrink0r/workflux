<?php

namespace Workflux;

use Params\ParametersTrait;
use Params\Parameters;
use Workflux\State\StateInterface;

/**
 * Standard implementation of the ExecutionContextInterface.
 */
class ExecutionContext implements ExecutionContextInterface
{
    use ParametersTrait;

    /**
     * @var string $state_machine_name
     */
    protected $state_machine_name;

    /**
     * @var string $current_state_name
     */
    protected $current_state_name;

    /**
     * Creates a new ExecutionContext instance.
     *
     * @param string $state_machine_name
     * @param string $current_state_name
     * @param array $attributes
     */
    public function __construct($state_machine_name, $current_state_name = null, array $parameters = [])
    {
        $this->state_machine_name = $state_machine_name;
        $this->current_state_name = $current_state_name;
        $this->parameters = new Parameters($parameters);
    }

    /**
     * Returns the name of the state machine, where the execution shall start/resume.
     *
     * @return string
     */
    public function getStateMachineName()
    {
        return $this->state_machine_name;
    }

    /**
     * Returns the name of the state machine state, where the execution shall resume.
     *
     * @return string
     */
    public function getCurrentStateName()
    {
        return $this->current_state_name;
    }

    /**
     * Sets the current state name.
     * Is called when the state machine enters a new state
     *
     * @param StateInterface $state
     */
    public function onStateEntry(StateInterface $state)
    {
        $this->current_state_name = $state->getName();
    }

    /**
     * Is called when the state machine exits it's state.
     *
     * @param StateInterface $state
     */
    public function onStateExit(StateInterface $state)
    {
    }
}
