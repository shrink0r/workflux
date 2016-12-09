<?php

namespace Workflux\Builder;

use Workflux\StateMachineInterface;

interface StateMachineBuilderInterface
{
    /**
     * @return StateMachineInterface
     */
    public function build(): StateMachineInterface;
}
