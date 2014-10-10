<?php

namespace Workflux;

use Workflux\State\StateInterface;

/**
 * ExecutionContextInterface reflects the current state of execution of a subject inside a state machine.
 * The StateMachineInterface relies on this interface to determine
 * where to continue with a suspended state machine execution.
 * It is also allows to share state machine variables across states in order to control flow.
 */
interface ExecutionContextInterface
{
    /**
     * Returns the name of the state machine, where the execution shall start/resume.
     *
     * @return string
     */
    public function getStateMachineName();

    /**
     * Returns the name of the state machine state, where the execution shall resume.
     *
     * @return string
     */
    public function getCurrentStateName();

    /**
     * Tells if the context has a specific parameter.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter($key);

    /**
     * Returns the value for the given parameter key.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed Returns either the parameter value or the given default, if the parameter isn't set.
     */
    public function getParameter($key, $default = null);

    /**
     * Returns execution context's parameters.
     *
     * @return mixed Returns either the parameter value or the given default, if the parameter isn't set.
     */
    public function getParameters();

    /**
     * Sets the given parameter.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $replace
     */
    public function setParameter($key, $value, $replace = true);

    /**
     * Removes a parameter given by key.
     *
     * @param string $key
     */
    public function removeParameter($key);

    /**
     * Clears all parameters.
     */
    public function clearParameters();

    /**
     * Sets the given parameters.
     *
     * @param array $parameters
     */
    public function setParameters($parameters);

    /**
     * Is called when the state machine enters a new state.
     *
     * @param StateInterface $state
     */
    public function onStateEntry(StateInterface $state);

    /**
     * Is called when the state machine exits it's state.
     *
     * @param StateInterface $state
     */
    public function onStateExit(StateInterface $state);
}
