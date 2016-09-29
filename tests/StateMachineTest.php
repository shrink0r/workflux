<?php

namespace Workflux\Tests;

use Workflux\Param\Input;
use Workflux\StateMachine;
use Workflux\State\Breakpoint;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\State;
use Workflux\State\StateSet;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionSet;

class StateMachineTest extends TestCase
{
    public function testConstruct()
    {
        $states = new StateSet([
            new InitialState('initial'),
            new Breakpoint('foobar'),
            new State('bar'),
            new FinalState('final')
        ]);

        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'foobar'))
            ->add(new Transition('foobar', 'bar'))
            ->add(new Transition('bar', 'final'));

        $statemachine = new StateMachine($states, $transitions);
        $output = $statemachine->execute(new Input, 'initial');
        $output = $statemachine->execute(Input::fromOutput($output), $output->getCurrentState());

        $this->assertEquals('final', $output->getCurrentState());
    }
}
