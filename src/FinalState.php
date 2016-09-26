<?php

namespace Workflux;

class FinalState extends State
{
    public function isFinal(): bool
    {
        return true;
    }
}
