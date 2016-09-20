<?php

namespace Workflux;

class State implements StateInterface
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input)
    {
        return Output::fromInput($this->name, $input);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function isInitial()
    {
        return false;
    }

    public function isFinal()
    {
        return false;
    }

    public function isBreakpoint()
    {
        return false;
    }
}
