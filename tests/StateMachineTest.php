<?php

namespace Workflux\Tests;

use Workflux\Breakpoint;
use Workflux\FinalState;
use Workflux\InitialState;
use Workflux\Input;
use Workflux\State;
use Workflux\StateMachine;
use Workflux\Transition;

class StateMachineTest extends TestCase
{
    public function testConstruct()
    {
        $states = [ new InitialState('initial'), new Breakpoint('foobar'), new FinalState('final') ];
        $transitions = [ new Transition('initial', 'foobar'), new Transition('foobar', 'final') ];

        $statemachine = new StateMachine($states, $transitions);
        $output = $statemachine->execute(new Input, 'initial');
        $output = $statemachine->execute(Input::fromOutput($output), $output->getCurrentState());

        var_dump($output);
    }
}
