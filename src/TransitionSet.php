<?php

namespace Workflux;

use Countable;
use Ds\Set;
use IteratorAggregate;
use Traversable;

class TransitionSet implements IteratorAggregate, Countable
{
    /**
     * @var Set $interal_set
     */
    private $internal_set;

    /**
     * @param TransitionInterface[] $transitions
     */
    public function __construct(array $transitions = [])
    {
        $this->internal_set = new Set(
            (function (TransitionInterface ...$transitions) {
                return $transitions;
            })(...$transitions)
        );
    }

    /**
     * @param TransitionInterface $transition
     *
     * @return TransitionSet
     */
    public function add(TransitionInterface $transition): TransitionSet
    {
        $transitions = $this->internal_set->toArray();
        $transitions[] = $transition;

        return new static($transitions);
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return $this->internal_set->getIterator();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->internal_set->count();
    }

    /**
     * @return TransitionInterface[]
     */
    public function toArray()
    {
        return $this->internal_set->toArray();
    }
}
