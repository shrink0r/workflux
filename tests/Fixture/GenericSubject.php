<?php

namespace Workflux\Tests\Fixture;

use Workflux\IStatefulSubject;
use Workflux\ExecutionState;

class GenericSubject implements IStatefulSubject
{
    protected $state_machine_name;

    protected $current_state_name;

    public function __construct($state_machine_name, $current_state_name)
    {
        $this->state_machine_name = $state_machine_name;
        $this->current_state_name = $current_state_name;
    }

    public function getExecutionState()
    {
        return new ExecutionState($this->state_machine_name, $this->current_state_name);
    }
}
