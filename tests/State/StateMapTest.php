<?php

namespace Workflux\Tests\State;

use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\StateInterface;
use Workflux\State\StateMap;
use Workflux\Tests\TestCase;

final class StateMapTest extends TestCase
{
    public function testCount()
    {
        $state_map = new StateMap($this->buildStateArray());
        $this->assertCount(5, $state_map);
    }

    public function testPut()
    {
        $state_map = (new StateMap)->put($this->createState('initial', InitialState::CLASS));
        $this->assertCount(1, $state_map);
    }

    public function testGet()
    {
        $state_map = new StateMap([
            $this->createState('initial', InitialState::CLASS),
            $foo_state = $this->createState('foo')
        ]);
        $this->assertEquals($foo_state, $state_map->get('foo'));
    }

    public function testHas()
    {
        $state_map = new StateMap([
            $this->createState('state1'),
            $this->createState('state2')
        ]);
        $this->assertTrue($state_map->has('state1'));
        $this->assertFalse($state_map->has('state3'));
    }

    public function testFind()
    {
        $state_map = (new StateMap($this->buildStateArray()))->find(function (StateInterface $state) {
            return !$state->isFinal() && !$state->isInitial();
        });
        $this->assertCount(3, $state_map);
    }

    public function testFindOne()
    {
        $state_map = new StateMap($this->buildStateArray());
        $bar_state = $state_map->findOne(function (StateInterface $state) {
            return $state->getName() === 'bar';
        });
        $this->assertEquals($state_map->get('bar'), $bar_state);
        $unknown_state = $state_map->findOne(function (StateInterface $state) {
            return $state->getName() === 'snafu';
        });
        $this->assertNull($unknown_state);
    }

    public function testGetIterator()
    {
        $state_map = new StateMap($this->buildStateArray());
        $i = 0;
        foreach ($state_map as $state_name => $state) {
            $this->assertEquals($state_name, $state->getName());
            $i++;
        }
        $this->assertEquals(5, $i);
    }

    public function testToArray()
    {
        $states = $this->buildStateArray();
        $state_map = new StateMap($states);
        $expected_array = array_combine(
            array_map(function (StateInterface $state) {
                return $state->getName();
            }, $states),
            $states
        );
        $this->assertEquals($expected_array, $state_map->toArray());
    }

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
