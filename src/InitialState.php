<?php

namespace Workflux;

class InitialState extends State
{
    /**
     * @return bool
     */
    public function isInitial(): bool
    {
        return true;
    }
}
