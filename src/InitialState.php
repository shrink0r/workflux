<?php

namespace Workflux;

class InitialState extends State
{
    public function isInitial(): bool
    {
        return true;
    }
}
