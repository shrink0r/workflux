<?php

namespace Workflux\Renderer;

use Workflux\StateMachineInterface;

interface RendererInterface
{
    /**
     * @param  StateMachineInterface $state_machine
     *
     * @return mixed
     */
    public function render(StateMachineInterface $state_machine);
}
