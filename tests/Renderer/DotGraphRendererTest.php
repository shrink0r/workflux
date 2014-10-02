<?php

namespace Workflux\Tests\Renderer;

use Workflux\Tests\BaseTestCase;
use Workflux\StateMachine\IStateMachine;
use Workflux\StateMachine\StateMachine;
use Workflux\StateMachine\StateMachineBuilder;
use Workflux\State\IState;
use Workflux\State\State;
use Workflux\Transition\Transition;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Renderer\DotGraphRenderer;

class DotGraphRendererTest extends BaseTestCase
{
    public function testRenderGraph()
    {
        $subject = new GenericSubject('test_machine', 'state1');

        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('approval'),
            new State('published'),
            new State('deleted', IState::TYPE_FINAL)
        ];

        $transitons = [
            new Transition('promote', [ 'editing' ], 'approval'),
            new Transition('promote', [ 'approval' ], 'published'),
            new Transition('delete', [ 'editing', 'approval', 'published' ], 'deleted'),
            new Transition('demote', [ 'published', 'approval' ], 'editing')
        ];

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName($subject->getStateMachineName())
            ->addStates($states)
            ->addTransitions($transitons)
            ->createStateMachine();

        $renderer = new DotGraphRenderer();
        $dot_code = $renderer->renderGraph($state_machine);

        $expected_code = <<<DOT
digraph test_machine {
node1 [label="editing"]
node2 [label="approval"]
node3 [label="published"]
node4 [label="deleted"]

node1 -> node2 [label="promote"]
node1 -> node4 [label="delete"]
node2 -> node3 [label="promote"]
node2 -> node4 [label="delete"]
node2 -> node1 [label="demote"]
node3 -> node4 [label="delete"]
node3 -> node1 [label="demote"]
}
DOT;

        $this->assertEquals($expected_code, $dot_code);
    }
}
