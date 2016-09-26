<?php

namespace Workflux;

class Breakpoint extends State
{
    /**
     * @return bool
     */
    public function isBreakpoint(): bool
    {
        return true;
    }
}
