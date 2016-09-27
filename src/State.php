<?php

namespace Workflux;

final class State implements StateInterface
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
        return false;
    }
}
