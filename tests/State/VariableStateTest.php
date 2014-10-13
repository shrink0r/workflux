<?php

namespace Workflux\Tests\State;

use Workflux\Error\Error;
use Workflux\Tests\BaseTestCase;
use Workflux\State\StateInterface;
use Workflux\State\VariableState;
use Workflux\Tests\Fixture\GenericSubject;

class VariableStateTest extends BaseTestCase
{
    public function testOnEntry()
    {
        $state_vars = [ 'foo' => 42 ];
        $state = new VariableState('state1', StateInterface::TYPE_INITIAL, [ 'variables' => $state_vars ]);

        $subject = new GenericSubject('test_machine', 'state1');
        $this->assertEquals([], $subject->getExecutionContext()->getParameters()->toArray());

        $state->onEntry($subject);
        $this->assertEquals($state_vars, $subject->getExecutionContext()->getParameters()->toArray());

        $state->onExit($subject);
        $this->assertEquals($state_vars, $subject->getExecutionContext()->getParameters()->toArray());
    }

    public function testOnExit()
    {
        $state_vars = [ 'foo' => 42 ];
        $state = new VariableState(
            'state1',
            StateInterface::TYPE_INITIAL,
            [ 'variables' => $state_vars, 'remove_variables' => array_keys($state_vars) ]
        );

        $subject = new GenericSubject('test_machine', 'state1');
        $this->assertEquals([], $subject->getExecutionContext()->getParameters()->toArray());

        $state->onEntry($subject);
        $this->assertEquals($state_vars, $subject->getExecutionContext()->getParameters()->toArray());

        $state->onExit($subject);
        $this->assertEquals([], $subject->getExecutionContext()->getParameters()->toArray());
    }
}
