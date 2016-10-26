<?php

namespace Workflux\State;

use Workflux\Param\InputInterface;
use Workflux\Param\Output;
use Workflux\Param\OutputInterface;
use Workflux\Param\ParamHolderInterface;
use Workflux\State\StateInterface;

abstract class AbstractState implements StateInterface
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var ParamHolderInterface $settings
     */
    private $settings;

    /**
     * @param string $name
     * @param ParamHolderInterface $settings
     */
    public function __construct(string $name, ParamHolderInterface $settings)
    {
        $this->name = $name;
        $this->settings = $settings;
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

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getSetting(string $name, $default = null)
    {
        return $this->settings->get($name) ?: $default;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasSetting(string $name): bool
    {
        return $this->settings->has($name);
    }

    /**
     * @return ParamHolderInterface
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
