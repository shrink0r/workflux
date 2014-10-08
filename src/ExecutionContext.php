<?php

namespace Workflux;

use Params\ParametersTrait;
use Workflux\State\StateInterface;

class ExecutionContext implements ExecutionContextInterface
{
    use ParametersTrait;

    protected $state_machine_name;

    protected $current_state_name;

    protected $attributes;

    public function __construct($state_machine_name, $current_state_name, array $attributes = [])
    {
        $this->state_machine_name = $state_machine_name;
        $this->current_state_name = $current_state_name;
        $this->attributes = $attributes;
    }

    public function getStateMachineName()
    {
        return $this->state_machine_name;
    }

    public function getCurrentStateName()
    {
        return $this->current_state_name;
    }

    public function onStateEntry(StateInterface $state)
    {
        $this->current_state_name = $state->getName();
    }

    public function onStateExit(StateInterface $state)
    {
        // echo PHP_EOL . $this->getName() . ' -> exiting' . PHP_EOL;
    }
}
