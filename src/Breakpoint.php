<?php

namespace Workflux;

class Breakpoint extends State
{
    public function isBreakpoint(): bool
    {
        return true;
    }
}
