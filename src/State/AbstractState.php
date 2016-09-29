<?php

namespace Workflux\State;

use Workflux\Param\InputInterface;
use Workflux\Param\Output;
use Workflux\Param\OutputInterface;
use Workflux\State\StateInterface;

abstract class AbstractState implements StateInterface
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input): OutputInterface
    {
        return Output::fromInput($this->name, $input);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isInitial(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isFinal(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isBreakpoint(): bool
    {
        return false;
    }
}
