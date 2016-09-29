<?php

namespace Workflux\State;

use Workflux\State\AbstractState;

final class Breakpoint extends AbstractState
{
    /**
     * @return bool
     */
    public function isBreakpoint(): bool
    {
        return true;
    }
}
