<?php

namespace Workflux\Tests\Transition;

use Workflux\Tests\BaseTestCase;
use Workflux\Transition\Transition;

class TransitionTest extends BaseTestCase
{
    public function testConstructorAndGetters()
    {
        $transition = new Transition('state1', 'state2');

        $incoming_state_names = $transition->getIncomingStateNames();
        $this->assertContains('state1', $incoming_state_names);
        $this->assertEquals('state2', $transition->getOutgoingStateName());
    }
}
