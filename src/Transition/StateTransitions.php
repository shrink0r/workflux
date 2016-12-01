<?php

namespace Workflux\Transition;

use Countable;
use Ds\Map;
use IteratorAggregate;
use Traversable;
use Workflux\Error\InvalidStructure;
use Workflux\State\StateMap;
use Workflux\Transition\TransitionInterface;
use Workflux\Transition\TransitionSet;

final class StateTransitions implements IteratorAggregate, Countable
{
    /**
     * @var Map $internal_map
     */
    private $internal_map;

    /**
     * @param  StateMap $states
     * @param  TransitionSet $transitions
     *
     * @return self
     */
    public static function create(StateMap $states, TransitionSet $transitions): self
    {
        $state_transitions = new self;
        foreach ($transitions as $transition) {
            $from_state = $transition->getFrom();
            $to_state = $transition->getTo();
            if (!$states->has($from_state)) {
                throw new InvalidStructure('Trying to transition from unknown state: '.$from_state);
            }
            if ($states->get($from_state)->isFinal()) {
                throw new InvalidStructure('Trying to transition from final-state: '.$from_state);
            }
            if (!$states->has($to_state)) {
                throw new InvalidStructure('Trying to transition to unknown state: '.$to_state);
            }
            if ($states->get($to_state)->isInitial()) {
                throw new InvalidStructure('Trying to transition to initial-state: '.$to_state);
            }
            $state_transitions = $state_transitions->put($transition);
        }
        return $state_transitions;
    }

    /**
     * @param TransitionInterface[] $states
     */
    public function __construct(array $transitions = [])
    {
        $this->internal_map = new Map;
        (function (TransitionInterface ...$transitions) {
            foreach ($transitions as $transition) {
                $state_transitions = $this->internal_map->get($transition->getFrom(), new TransitionSet);
                $this->internal_map->put($transition->getFrom(), $state_transitions->add($transition));
            }
        })(...$transitions);
    }

    /**
     * @param TransitionInterface $transition
     *
     * @return self
     */
    public function put(TransitionInterface $transition): self
    {
        $cloned_self = clone $this;
        $cloned_self->internal_map->put(
            $transition->getFrom(),
            $cloned_self->get($transition->getFrom())->add($transition)
        );

        return $cloned_self;
    }

    /**
     * @param string $state_name
     *
     * @return bool
     */
    public function has(string $state_name): bool
    {
        return $this->internal_map->hasKey($state_name);
    }

    /**
     * @param string $state_name
     *
     * @return TransitionSet
     */
    public function get(string $state_name): TransitionSet
    {
        return $this->internal_map->get($state_name, new TransitionSet);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->internal_map->count();
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return $this->internal_map->getIterator();
    }

    /**
     * @return StateInterface[]
     */
    public function toArray()
    {
        return $this->internal_map->toArray();
    }

    public function __clone()
    {
        $this->internal_map = clone $this->internal_map;
    }
}
