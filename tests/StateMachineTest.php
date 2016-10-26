<?php

namespace Workflux\Tests;

use Workflux\Error\CorruptExecutionFlow;
use Workflux\Param\Input;
use Workflux\Param\Settings;
use Workflux\StateMachine;
use Workflux\State\Breakpoint;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\State;
use Workflux\State\StateSet;
use Workflux\Tests\TestCase;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionSet;

class StateMachineTest extends TestCase
{
    public function testConstruct()
    {
        $states = new StateSet([
            new InitialState('initial', new Settings),
            new Breakpoint('foobar', new Settings),
            new State('bar', new Settings),
            new FinalState('final', new Settings)
        ]);

        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'foobar'))
            ->add(new Transition('foobar', 'bar'))
            ->add(new Transition('bar', 'final'));

        $statemachine = new StateMachine('test-machine', $states, $transitions);
        $output = $statemachine->execute(new Input, 'initial');
        $output = $statemachine->execute(Input::fromOutput($output), $output->getCurrentState());

        $this->assertEquals('final', $output->getCurrentState());
    }

    public function testInfiniteExecutionLoop()
    {
        $this->expectException(CorruptExecutionFlow::CLASS);
        $this->expectExceptionMessage('Trying to execute more than the allowed number of 20 workflow steps.
Looks like there is a loop between: approval -> published -> archive');

        $states = new StateSet([
            new InitialState('initial', new Settings),
            new State('edit', new Settings),
            new State('approval', new Settings),
            new State('published', new Settings),
            new State('archive', new Settings),
            new FinalState('final', new Settings)
        ]);

        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'edit'))
            ->add(new Transition('edit', 'approval'))
            ->add(new Transition('approval', 'published'))
            ->add(new Transition('published', 'archive'))
            ->add(new Transition('archive', 'approval'))
            ->add(new InactiveTransition('archive', 'final'));

        $statemachine = new StateMachine('test-machine', $states, $transitions);
        $statemachine->execute(new Input, 'initial');
    }
}
