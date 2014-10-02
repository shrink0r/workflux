<?php

namespace Workflux\Tests\State;

use Workflux\Error;
use Workflux\Tests\BaseTestCase;
use Workflux\State\IState;
use Workflux\State\State;

class StateTest extends BaseTestCase
{
    public function testConstructorAndGetters()
    {
        $state = new State('state1', IState::TYPE_INITIAL);

        $this->assertEquals('state1', $state->getName());
        $this->assertEquals(IState::TYPE_INITIAL, $state->getType());
        $this->assertTrue($state->isInitial());
        $this->assertFalse($state->isActive());
        $this->assertFalse($state->isFinal());
    }

    public function testInvalidType()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Invalid state type "shouldnt_work" given. Only the types initial, active, final are permitted.'
        );

        new State('state1', 'shouldnt_work');
    }
}
