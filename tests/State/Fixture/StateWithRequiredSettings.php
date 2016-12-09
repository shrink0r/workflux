<?php

namespace Workflux\Tests\State\Fixture;

use Workflux\State\StateInterface;
use Workflux\State\StateTrait;

class StateWithRequiredSettings implements StateInterface
{
    use StateTrait;

    private function getRequiredSettings()
    {
        return [ 'foobar' ];
    }
}
