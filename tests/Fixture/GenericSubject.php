<?php

namespace Workflux\Tests\Fixture;

use Workflux\IStatefulSubject;

class GenericSubject implements IStatefulSubject
{
    protected $state_machine_name;

    protected $current_state_name;

    public function __construct($state_machine_name, $current_state_name)
    {
        $this->state_machine_name = $state_machine_name;
        $this->current_state_name = $current_state_name;
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
