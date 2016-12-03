<?php

namespace Workflux\Transition;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\Param\ParamHolderInterface;
use Workflux\Transition\TransitionInterface;

final class Transition implements TransitionInterface
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @param string $from
     * @param string $to
     * @param string $label
     * @param array $constraints
     */
    public function __construct(string $from, string $to, ParamHolderInterface $settings, array $constraints = [])
    {
        $this->from = $from;
        $this->to = $to;
        $this->settings = $settings;
        $this->constraints = $constraints;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->settings->get('label') ?: '';
    }

    /**
     * @return array
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return bool
     */
    public function hasConstraints(): bool
    {
        return !empty($this->constraints);
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     *
     * @return bool
     */
    public function isActivatedBy(InputInterface $input, OutputInterface $output): bool
    {
        foreach ($this->constraints as $constraint) {
            if (!$constraint->accepts($input, $output)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $label = implode("\nand ", $this->constraints);
        return empty($label) ? $this->getLabel() : $label;
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
