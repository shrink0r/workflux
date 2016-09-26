<?php

namespace Workflux;

use Ds\Set;
use Traversable;
use IteratorAggregate;

class StateSet implements IteratorAggregate
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
     * @param StateInterface
     *
     * @return StateSet
     */
    public function add(StateInterface $state): StateSet
    {
        $this->internal_set->add($state);
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
    public function toArray()
    {
        return $this->internal_set->toArray();
    }
}
