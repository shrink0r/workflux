<?php

namespace Workflux\State;

use Shrink0r\PhpSchema\Error;
use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;
use Workflux\Error\InputError;
use Workflux\Error\OutputError;
use Workflux\Error\WorkfluxError;
use Workflux\Param\InputInterface;
use Workflux\Param\Output;
use Workflux\Param\OutputInterface;
use Workflux\Param\ParamHolderInterface;

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
     * @var SchemaInterface $input_schema
     */
    private $input_schema;

    /**
     * @var SchemaInterface $output_schema
     */
    private $output_schema;

    /**
     * @param string $name
     * @param ParamHolderInterface $settings
     */
    public function __construct(
        string $name,
        ParamHolderInterface $settings,
        SchemaInterface $input_schema,
        SchemaInterface $output_schema
    ) {
        $this->name = $name;
        $this->settings = $settings;
        $this->input_schema = $input_schema;
        $this->output_schema = $output_schema;
        foreach ($this->getRequiredSettings() as $setting_name) {
            if (!$this->settings->has($setting_name)) {
                throw new WorkfluxError("Trying to configure state '$name' without required setting '$setting_name'.");
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
        $result = $this->input_schema->validate($input->toArray());
        if ($result instanceof Error) {
            throw new InputError(
                $result->unwrap(),
                sprintf("Trying to execute state '%s' with invalid input.", $this->getName())
            );
        }
        $output = $this->generateOutput($input);
        $result = $this->output_schema->validate($output->toArray()['params']);
        if ($result instanceof Error) {
            throw new OutputError(
                $result->unwrap(),
                sprintf("Trying to return invalid output from state: '%s'", $this->getName())
            );
        }
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
     * @return SchemaInterface
     */
    public function getInputSchema(): SchemaInterface
    {
        return $this->input_schema;
    }

    /**
     * @return SchemaInterface
     */
    public function getOutputSchema(): SchemaInterface
    {
        return $this->output_schema;
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
        $params = [];
        foreach ($this->getSetting('output', []) as $key => $value) {
            if (is_string($value) && preg_match('/\$\{(.+)\}/', $value, $matches)) {
                $value = $input->get($matches[1]);
            }
            $params[$key] = $value;
        }
        return new Output($this->name, $params);
    }
}
