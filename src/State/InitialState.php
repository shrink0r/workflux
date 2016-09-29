<?php

namespace Workflux\State;

use Workflux\State\AbstractState;

final class InitialState extends AbstractState
{
    /**
     * @return bool
     */
    public function isInitial(): bool
    {
        return true;
    }
}
