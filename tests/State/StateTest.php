<?php

namespace Workflux\Tests\State;

use Workflux\Error\Error;
use Workflux\Tests\BaseTestCase;
use Workflux\State\StateInterface;
use Workflux\State\State;

class StateTest extends BaseTestCase
{
    public function testConstructorAndGetters()
    {
        $state = new State('state1', StateInterface::TYPE_INITIAL, [ 'test_option' => 42 ]);

        $this->assertEquals('state1', $state->getName());
        $this->assertEquals(StateInterface::TYPE_INITIAL, $state->getType());
        $this->assertEquals(42, $state->getOption('test_option'));
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
