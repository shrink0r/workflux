<?php

namespace Workflux\State;

use Countable;
use Ds\Set;
use IteratorAggregate;
use Traversable;
use Workflux\Error\InvalidStructure;
use Workflux\State\StateInterface;
use Workflux\State\StateMap;

final class StateSet implements IteratorAggregate, Countable
{
    /**
     * @var Set $internal_set
     */
    private $internal_set;

    /**
     * @param StateInterface[] $states
     */
    public function __construct(array $states = [])
    {
        $this->internal_set = new Set(
            (function (StateInterface ...$states) {
                return $states;
            })(...$states)
        );
    }

    /**
     * @return []
     */
    public function splat(): array
    {
        $initial_state = null;
        $all_states = new StateMap;
        $final_states = new StateMap;
        foreach ($this->internal_set as $state) {
            if ($state->isInitial()) {
                if ($initial_state !== null) {
                    throw new InvalidStructure('Trying to add more than one initial state.');
                }
                $initial_state = $state;
            }
            if ($state->isFinal()) {
                if ($state->isInitial()) {
                    throw new InvalidStructure('Trying to add state as initial and final at the same time.');
                }
                $final_states = $final_states->put($state);
            }
            $all_states = $all_states->put($state);
        }
        if (!$initial_state) {
            throw new InvalidStructure('Trying to create statemachine without an initial state.');
        }
        if ($final_states->count() === 0) {
            throw new InvalidStructure('Trying to create statemachine without at least one final state.');
        }
        return [ $initial_state, $all_states, $final_states ];
    }

    /**
     * @param StateInterface
     *
     * @return self
     */
    public function add(StateInterface $state): self
    {
        $cloned_set = clone $this;
        $cloned_set->internal_set->add($state);

        return $cloned_set;
    }

    /**
     * @param StateInterface $state
     *
     * @return bool
     */
    public function contains(StateInterface $state): bool
    {
        return $this->internal_set->contains($state);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->internal_set->count();
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return $this->internal_set->getIterator();
    }

    /**
     * @return StateInterface[]
     */
    public function toArray(): array
    {
        return $this->internal_set->toArray();
    }

    public function __clone()
    {
        $this->internal_set = clone $this->internal_set;
    }
}
