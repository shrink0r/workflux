<?php

namespace Workflux;

use Params\ParametersTrait;

class ExecutionState implements IExecutionState
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
}
