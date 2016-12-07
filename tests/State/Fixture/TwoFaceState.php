<?php

namespace Workflux\Tests\State\Fixture;

use Workflux\State\StateInterface;
use Workflux\State\StateTrait;

class TwoFaceState implements StateInterface
{
    use StateTrait;

    public function isInitial(): bool
    {
        return true;
    }

    public function isFinal(): bool
    {
        return true;
    }
}
