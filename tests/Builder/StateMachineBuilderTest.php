<?php

namespace Workflux\Tests\Builder;

use Workflux\Builder\StateMachineBuilder;
use Workflux\StateMachineInterface;
use Workflux\State\Breakpoint;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\State;
use Workflux\Tests\TestCase;
use Workflux\Transition\Transition;

class StateMachineBuilderTest extends TestCase
{
    public function testBuild()
    {
        $state_machine = (new StateMachineBuilder)
            ->addStateMachineName('video-transcoding')
            ->addState(new InitialState('initial'))
            ->addStates([
                new Breakpoint('foobar'),
                new State('bar'),
                new FinalState('final')
            ])
            ->addTransition(new Transition('initial', 'foobar'))
            ->addTransitions([
                new Transition('foobar', 'bar'),
                new Transition('bar', 'final')
            ])
            ->build();

        $this->assertInstanceOf(StateMachineInterface::CLASS, $state_machine);
    }
}
