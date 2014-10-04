<?php

namespace Workflux\Tests\Renderer;

use Workflux\Tests\BaseTestCase;
use Workflux\StateMachine\IStateMachine;
use Workflux\StateMachine\StateMachine;
use Workflux\Builder\StateMachineBuilder;
use Workflux\State\IState;
use Workflux\State\State;
use Workflux\Transition\Transition;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Renderer\DotGraphRenderer;

class DotGraphRendererTest extends BaseTestCase
{
    public function testRenderGraph()
    {
        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('approval'),
            new State('published'),
            new State('deleted', IState::TYPE_FINAL)
        ];

        $approve = new Transition('editing', 'approval');
        $publish = new Transition('approval', 'published');
        $demote = new Transition([ 'approval', 'published' ], 'editing');
        $delete = new Transition([ 'editing', 'approval', 'published' ], 'deleted');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName('test_machine')
            ->addStates($states)
            ->addTransitions([ 'promote' => [ $approve, $publish ], 'demote' => $demote, 'delete' => $delete ])
            ->build();

        $renderer = new DotGraphRenderer();
        $dot_code = $renderer->renderGraph($state_machine);

        $expected_code = <<<DOT
digraph test_machine {
node1 [label="editing" fontcolor="#000000" color="#607d8b"]
node2 [label="approval" fontcolor="#000000" color="#607d8b"]
node3 [label="published" fontcolor="#000000" color="#607d8b"]
node4 [label="deleted" fontcolor="#000000" color="#607d8b" style="bold"]
0 [label="X" fontsize="13" margin="0" fontname="arial" width="0.15" color="#607d8b" shape="circle"]

node1 -> node2 [label="promote" fontcolor="#7f8c8d" color="#2ecc71"]
node1 -> node4 [label="delete" fontcolor="#7f8c8d" color="#607d8b"]
node2 -> node3 [label="promote" fontcolor="#7f8c8d" color="#2ecc71"]
node2 -> node1 [label="demote" fontcolor="#7f8c8d" color="#3498db"]
node2 -> node4 [label="delete" fontcolor="#7f8c8d" color="#607d8b"]
node3 -> node1 [label="demote" fontcolor="#7f8c8d" color="#3498db"]
node3 -> node4 [label="delete" fontcolor="#7f8c8d" color="#607d8b"]
0 -> node1 [color="#607d8b"]
}
DOT;

        $this->assertEquals($expected_code, $dot_code);
    }
}
