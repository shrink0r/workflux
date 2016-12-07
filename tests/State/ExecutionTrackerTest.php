<?php

namespace Workflux\Tests\State;

use Workflux\StateMachine;
use Workflux\State\ExecutionTracker;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\StateSet;
use Workflux\Tests\TestCase;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionSet;

final class ExecutionTrackerTest extends TestCase
{
    public function testTrack()
    {
        $state_machine = $this->createStateMachine();
        $states = $state_machine->getStates();
        $tracker = new ExecutionTracker($state_machine);
        $this->assertEquals(1, $tracker->track($states->get('initial')));
        $this->assertEquals(1, $tracker->track($states->get('foo')));
        $this->assertEquals(1, $tracker->track($states->get('bar')));
        $this->assertEquals(1, $tracker->track($states->get('foobar')));
        $this->assertEquals(2, $tracker->track($states->get('foo')));
        $this->assertEquals(
            [ 'initial', 'foo', 'bar', 'foobar', 'foo' ],
            $tracker->getBreadcrumbs()->toArray()
        );
    }

    public function testGetExecutionCount()
    {
        $state_machine = $this->createStateMachine();
        $states = $state_machine->getStates();
        $tracker = new ExecutionTracker($state_machine);
        $tracker->track($states->get('initial'));
        $tracker->track($states->get('foo'));
        $tracker->track($states->get('bar'));
        $tracker->track($states->get('foobar'));
        $tracker->track($states->get('foo'));
        $this->assertEquals(0, $tracker->getExecutionCount($states->get('final')));
        $this->assertEquals(1, $tracker->getExecutionCount($states->get('initial')));
        $this->assertEquals(2, $tracker->getExecutionCount($states->get('foo')));
    }

    public function testDetectExecutionLoop()
    {
        $state_machine = $this->createStateMachine();
        $states = $state_machine->getStates();
        $tracker = new ExecutionTracker($state_machine);
        $tracker->track($states->get('initial'));
        for ($i = 0; $i < StateMachine::MAX_CYCLES + 1; $i++) {
            $tracker->track($states->get('foo'));
            $tracker->track($states->get('bar'));
            $tracker->track($states->get('foobar'));
        }
        $this->assertEquals([ 'foo', 'bar', 'foobar' ], $tracker->detectExecutionLoop()->toArray());
    }

    private function createStateMachine()
    {
        $states = new StateSet([
            $this->createState('initial', InitialState::CLASS),
            $this->createState('foo'),
            $this->createState('bar'),
            $this->createState('foobar'),
            $this->createState('final', FinalState::CLASS)
        ]);
        $transitions = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        return new StateMachine('test-machine', $states, $transitions);
    }
}
