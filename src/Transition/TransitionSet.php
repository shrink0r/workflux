<?php

namespace Workflux\Transition;

use Countable;
use Ds\Set;
use IteratorAggregate;
use Traversable;
use Workflux\Transition\TransitionInterface;

final class TransitionSet implements IteratorAggregate, Countable
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
     * @return self
     */
    public function add(TransitionInterface $transition): self
    {
        $transitions = $this->internal_set->toArray();
        $transitions[] = $transition;

        return new static($transitions);
    }

    /**
     * @param TransitionInterface $transition
     *
     * @return bool
     */
    public function contains(TransitionInterface $transition): bool
    {
        foreach ($this->internal_set as $cur_transition) {
            if ($cur_transition->getFrom() === $transition->getFrom()
                && $cur_transition->getTo() === $transition->getTo()
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param callable $callback
     *
     * @return self
     */
    public function filter(callable $callback): self
    {
        $set = clone $this;
        $set->internal_set = $this->internal_set->filter($callback);

        return $set;
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
    public function toArray(): array
    {
        return $this->internal_set->toArray();
    }

    public function __clone()
    {
        $this->internal_set = clone $this->internal_set;
    }
}
