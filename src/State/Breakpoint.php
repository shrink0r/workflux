<?php

namespace Workflux\State;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\State\State;
use Workflux\State\StateInterface;

final class Breakpoint implements StateInterface
{
    /**
     * @var StateInterface $internal_state
     */
    private $internal_state;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->internal_state = new State($name);
    }

    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input): OutputInterface
    {
        return $this->internal_state->execute($input);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->internal_state->getName();
    }

    /**
     * @return boolean
     */
    public function isInitial(): bool
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isFinal(): bool
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isBreakpoint(): bool
    {
        return true;
    }
}
