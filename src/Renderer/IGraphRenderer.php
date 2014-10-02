<?php

namespace Workflux\Renderer;

use Workflux\StateMachine\IStateMachine;

interface IGraphRenderer
{
    public function renderGraph(IStateMachine $state_machine);
}
