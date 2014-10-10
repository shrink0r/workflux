<?php

namespace Workflux\Renderer;

use Workflux\StateMachine\StateMachineInterface;

/**
 * GraphRendererInterface implementations are expected to render StateMachineInterface instances into any kind of
 * specific output format, like for example xml, yaml or dot.
 */
interface GraphRendererInterface
{
    /**
     * Renders the given state machine to a specific format.
     *
     * @param StateMachineInterface $state_machine
     *
     * @return mixed
     */
    public function renderGraph(StateMachineInterface $state_machine);
}
