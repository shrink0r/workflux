<?php

namespace Workflux\State;

use Workflux\State\StateInterface;
use Workflux\State\StateTrait;

final class FinalState implements StateInterface
{
    use StateTrait;

    /**
     * @return bool
     */
    public function isFinal(): bool
    {
        return true;
    }
}
