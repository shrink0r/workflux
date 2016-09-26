<?php

namespace Workflux;

class FinalState extends State
{
    /**
     * @return bool
     */
    public function isFinal(): bool
    {
        return true;
    }
}
