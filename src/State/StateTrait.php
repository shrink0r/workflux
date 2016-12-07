<?php

namespace Workflux\State;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Workflux\Error\ConfigError;
use Workflux\Param\InputInterface;
use Workflux\Param\Output;
use Workflux\Param\OutputInterface;
use Workflux\Param\ParamHolderInterface;
use Workflux\State\ValidatorInterface;

trait StateTrait
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
     * @var ValidatorInterface $schemas
     */
    private $validator;

    /**
     * @var ExpressionLanguage $expression_engine
     */
    private $expression_engine;

    /**
     * @param string $name
     * @param ParamHolderInterface $settings
     * @param ValidatorInterface $validator
     * @param ExpressionLanguage $expression_engine
     */
    public function __construct(
        string $name,
        ParamHolderInterface $settings,
        ValidatorInterface $validator,
        ExpressionLanguage $expression_engine
    ) {
        $this->name = $name;
        $this->settings = $settings;
        $this->validator = $validator;
        $this->expression_engine = $expression_engine;
        foreach ($this->getRequiredSettings() as $setting_name) {
            if (!$this->settings->has($setting_name)) {
                throw new ConfigError("Trying to configure state '$name' without required setting '$setting_name'.");
            }
        }
    }

    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input): OutputInterface
    {
        $this->validator->validateInput($this, $input);
        $output = $this->generateOutput($input);
        $this->validator->validateOutput($this, $output);
        return $output;
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
    public function isInteractive(): bool
    {
        return false;
    }

     /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getSetting(string $name, $default = null)
    {
        return $this->settings->get($name) ?? $default;
    }

    /**
     * @return ParamHolderInterface
     */
    public function getSettings(): ParamHolderInterface
    {
        return $this->settings;
    }

    /**
     * @return string[]
     */
    private function getRequiredSettings(): array
    {
        return [];
    }

    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    private function generateOutput(InputInterface $input): OutputInterface
    {
        return new Output(
            $this->name,
            array_merge(
                $this->evaluateInputExports($input),
                $this->generateOutputParams($input)
            )
        );
    }

    /**
     * @param  InputInterface $input
     *
     * @return mixed[]
     */
    private function evaluateInputExports(InputInterface $input): array
    {
        $exports = [];
        foreach ($this->getSetting('output', []) as $key => $value) {
            $exports[$key] = $this->expression_engine->evaluate($value, [ 'input' => $input ]);
        }
        return $exports;
    }

    /**
     * @param  InputInterface $input
     *
     * @return mixed[]
     */
    private function generateOutputParams(InputInterface $input): array
    {
        return [];
    }
}
