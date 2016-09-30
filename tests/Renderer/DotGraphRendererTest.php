<?php

namespace Workflux\Tests\Renderer;

use Workflux\Renderer\DotGraphRenderer;
use Workflux\StateMachine;
use Workflux\State\Breakpoint;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\State;
use Workflux\State\StateSet;
use Workflux\Tests\TestCase;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionSet;

class DotGraphRendererTest extends TestCase
{
    public function testRenderer()
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

        $state_machine = new StateMachine('test-machine', $states, $transitions);
        $expected_graph = file_get_contents(__DIR__ . '/Fixture/testcase_1.dot');

        $renderer = new DotGraphRenderer;
        $this->assertEquals($expected_graph, $renderer->render($state_machine));
    }
}
