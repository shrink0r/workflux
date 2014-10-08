<?php

namespace Workflux\Renderer;

use Workflux\StateMachine\StateMachineInterface;

interface GraphRendererInterface
{
    public function renderGraph(StateMachineInterface $state_machine);
}
