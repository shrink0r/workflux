<?php

namespace Workflux\State;

use Workflux\State\AbstractState;

final class FinalState extends AbstractState
{
    /**
     * @return bool
     */
    public function isFinal(): bool
    {
        return true;
    }
}
