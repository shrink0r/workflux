<?php

namespace Workflux\State;

use Countable;
use Ds\Map;
use IteratorAggregate;
use Traversable;
use Workflux\State\StateInterface;

final class StateMap implements IteratorAggregate, Countable
{
    /**
     * @var Map $internal_map
     */
    private $internal_map;

    /**
     * @param StateInterface[] $states
     */
    public function __construct(array $states = [])
    {
        $this->internal_map = new Map;
        (function (StateInterface ...$states) {
            foreach ($states as $state) {
                 $this->internal_map->put($state->getName(), $state);
            }
        })(...$states);
    }

    /**
     * @param StateInterface
     *
     * @return self
     */
    public function put(StateInterface $state): self
    {
        $cloned_map = clone $this;
        $cloned_map->internal_map->put($state->getName(), $state);

        return $cloned_map;
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
     * @return StateInterface
     */
    public function get(string $state_name): StateInterface
    {
        return $this->internal_map->get($state_name);
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
    public function toArray(): array
    {
        return $this->internal_map->toArray();
    }

    public function __clone()
    {
        $this->internal_map = clone $this->internal_map;
    }
}
