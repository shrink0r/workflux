<?php

namespace Workflux\State;

use Ds\Set;
use IteratorAggregate;
use Traversable;
use Workflux\State\StateInterface;

final class StateSet implements IteratorAggregate
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
    public function toArray()
    {
        return $this->internal_set->toArray();
    }

    public function __clone()
    {
        $this->internal_set = clone $this->internal_set;
    }
}
