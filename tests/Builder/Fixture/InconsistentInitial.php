<?php

namespace Workflux\Tests\Builder\Fixture;

use Workflux\State\StateInterface;
use Workflux\State\StateTrait;

final class InconsistentInitial implements StateInterface
{
    use StateTrait;

    public function isInitial(): bool
    {
        return false;
    }
}
