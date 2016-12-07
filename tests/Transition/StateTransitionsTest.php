<?php

namespace Workflux\Tests\Transition;

use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\StateMap;
use Workflux\Tests\TestCase;
use Workflux\Transition\StateTransitions;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionSet;

final class StateTransitionsTest extends TestCase
{
    public function testCount()
    {
        $state_map = new StateMap($this->buildStateArray());
        $transition_set = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        $state_transitions = new StateTransitions($state_map, $transition_set);
        $this->assertCount(count($transition_set), $state_transitions);
    }

    public function testHas()
    {
        $state_map = new StateMap($this->buildStateArray());
        $transition_set = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        $state_transitions = new StateTransitions($state_map, $transition_set);
        $this->assertTrue($state_transitions->has('initial'));
        $this->assertFalse($state_transitions->has('baz'));
    }

    public function testToArray()
    {
        $state_map = new StateMap($this->buildStateArray());
        $transition_set = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        $state_transitions = new StateTransitions($state_map, $transition_set);
        $expected_transition_sets = [ 'initial', 'foo', 'bar', 'foobar' ];
        $state_transitions_array = $state_transitions->toArray();
        foreach ($expected_transition_sets as $expected_transition_set) {
            $this->assertInstanceOf(TransitionSet::CLASS, $state_transitions_array[$expected_transition_set]);
        }
    }

    /**
     * @expectedException Workflux\Error\InvalidStructure
     * @expectedExceptionMessage Trying to transition to unknown state: foobaz
     */
    public function testNonExistantToState()
    {
        $state_map = new StateMap($this->buildStateArray());
        $transition_set = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobaz'),
            new Transition('foobar', 'final')
        ]);
        new StateTransitions($state_map, $transition_set);
    } // @codeCoverageIgnore

    /**
     * @expectedException Workflux\Error\InvalidStructure
     * @expectedExceptionMessage Trying to transition from unknown state: fu
     */
    public function testNonExistantFromState()
    {
        $state_map = new StateMap($this->buildStateArray());
        $transition_set = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('fu', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        new StateTransitions($state_map, $transition_set);
    } // @codeCoverageIgnore

    /**
     * @expectedException Workflux\Error\InvalidStructure
     * @expectedExceptionMessage Trying to transition to initial-state: initial
     */
    public function testTransitionToInitialState()
    {
        $state_map = new StateMap($this->buildStateArray());
        $transition_set = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'initial'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        new StateTransitions($state_map, $transition_set);
    } // @codeCoverageIgnore

    /**
     * @expectedException Workflux\Error\InvalidStructure
     * @expectedExceptionMessage Trying to transition from final-state: final
     */
    public function testTransitionFromFinalState()
    {
        $state_map = new StateMap($this->buildStateArray());
        $transition_set = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final'),
            new Transition('final', 'foo')
        ]);
        new StateTransitions($state_map, $transition_set);
    } // @codeCoverageIgnore

    /**
     * @expectedException Workflux\Error\InvalidStructure
     * @expectedExceptionMessage Not all states are properly connected.
     */
    public function testStatesNotConnected()
    {
        $state_map = new StateMap($this->buildStateArray());
        $transition_set = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar')
        ]);
        new StateTransitions($state_map, $transition_set);
    } // @codeCoverageIgnore

    private function buildStateArray()
    {
        return [
            $this->createState('initial', InitialState::CLASS),
            $this->createState('foo'),
            $this->createState('bar'),
            $this->createState('foobar'),
            $this->createState('final', FinalState::CLASS)
        ];
    }
}
