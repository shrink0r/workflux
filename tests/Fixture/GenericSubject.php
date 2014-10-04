<?php

namespace Workflux\Tests\Fixture;

use Workflux\IStatefulSubject;
use Workflux\ExecutionContext;

class GenericSubject implements IStatefulSubject
{
    protected $state_machine_name;

    protected $current_state_name;

    protected $execution_context;

    public function __construct($state_machine_name, $current_state_name)
    {
        $this->state_machine_name = $state_machine_name;
        $this->current_state_name = $current_state_name;
        $this->execution_context = new ExecutionContext($this->state_machine_name, $this->current_state_name);
    }

    public function getExecutionContext()
    {
        return $this->execution_context;
    }
}
