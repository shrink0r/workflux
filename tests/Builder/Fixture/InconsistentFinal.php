<?php

namespace Workflux\Tests\Builder\Fixture;

use Workflux\State\StateInterface;
use Workflux\State\StateTrait;

final class InconsistentFinal implements StateInterface
{
    use StateTrait;

    public function isFinal(): bool
    {
        return false;
    }
}
