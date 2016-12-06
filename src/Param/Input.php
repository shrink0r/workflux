<?php

namespace Workflux\Param;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\Param\ParamHolderTrait;

final class Input implements InputInterface
{
    use ParamHolderTrait;

    /**
     * @var string $event
     */
    private $event;

    /**
     * @param mixed[] $params
     * @param string $event
     */
    public function __construct(array $params = [], string $event = '')
    {
        $this->params = $params;
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return boolean
     */
    public function hasEvent(): bool
    {
        return !empty($this->event);
    }

    /**
     * @param  string $event
     * @return InputInterface
     */
    public function withEvent(string $event): InputInterface
    {
        $clone = clone $this;
        $this->event = $event;
        return $clone;
    }

    /**
     * @param OutputInterface $output
     *
     * @return InputInterface
     */
    public static function fromOutput(OutputInterface $output): InputInterface
    {
        return new static($output->toArray()['params']);
    }
}
