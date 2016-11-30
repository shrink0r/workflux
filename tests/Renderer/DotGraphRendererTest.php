<?php

namespace Workflux\Tests\Renderer;

use Workflux\Param\Settings;
use Workflux\Renderer\DotGraphRenderer;
use Workflux\StateMachine;
use Workflux\State\InteractiveState;
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
            $this->createState('initial', InitialState::CLASS),
            $this->createState('foobar'),
            $this->createState('bar'),
            $this->createState('final', FinalState::CLASS)
        ]);

        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'foobar', new Settings))
            ->add(new Transition('foobar', 'bar', new Settings))
            ->add(new Transition('bar', 'final', new Settings));

        $state_machine = new StateMachine('test-machine', $states, $transitions);
        $expected_graph = file_get_contents(__DIR__ . '/Fixture/testcase_1.dot');

        $renderer = new DotGraphRenderer;
        $this->assertEquals($expected_graph, $renderer->render($state_machine));
    }
}
