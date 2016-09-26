<?php

namespace Workflux\Tests;

use Workflux\Breakpoint;
use Workflux\FinalState;
use Workflux\InitialState;
use Workflux\Input;
use Workflux\State;
use Workflux\StateMachine;
use Workflux\StateSet;
use Workflux\Transition;
use Workflux\TransitionSet;

class StateMachineTest extends TestCase
{
    public function testConstruct()
    {
        $states = new StateSet([
            new InitialState('initial'),
            new Breakpoint('foobar'),
            new FinalState('final')
        ]);

        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'foobar'))
            ->add(new Transition('foobar', 'final'));

        $statemachine = new StateMachine($states, $transitions);
        $output = $statemachine->execute(new Input, 'initial');
        $output = $statemachine->execute(Input::fromOutput($output), $output->getCurrentState());

        $this->assertEquals('final', $output->getCurrentState());
    }
}
