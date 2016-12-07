<?php

namespace Workflux\Builder;

use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;
use Symfony\Component\Yaml\Parser;
use Workflux\Builder\Factory;
use Workflux\Builder\StateMachineBuilderInterface;
use Workflux\Error\ConfigError;
use Workflux\StateMachine;
use Workflux\StateMachineInterface;

final class YamlStateMachineBuilder implements StateMachineBuilderInterface
{
    /**
     * @var Parser $parser
     */
    private $parser;

    /**
     * @var string $yaml_filepath
     */
    private $yaml_filepath;

    /**
     * @var FactoryInterface $factory
     */
    private $factory;

    /**
     * @param string $yaml_filepath
     * @param FactoryInterface|null $factory
     */
    public function __construct(string $yaml_filepath, FactoryInterface $factory = null)
    {
        $this->parser = new Parser;
        if (!is_readable($yaml_filepath)) {
            throw new ConfigError("Trying to load non-existant statemachine definition at: $yaml_filepath");
        }
        $this->yaml_filepath = $yaml_filepath;
        $this->schema = new StateMachineSchema;
        $this->factory = $factory ?? new Factory;
    }

    /**
     * @return StateMachineInterface
     */
    public function build(): StateMachineInterface
    {
        $data = $this->parser->parse(file_get_contents($this->yaml_filepath));
        $result = (new StateMachineSchema)->validate($data);
        if ($result instanceof Error) {
            throw new ConfigError('Invalid statemachine configuration given: '.print_r($result->unwrap(), true));
        }
        list($states, $transitions) = $this->realizeConfig($data['states']);
        $state_machine_class = Maybe::unit($data)->class->get() ?? StateMachine::CLASS;
        return (new StateMachineBuilder($state_machine_class))
            ->addStateMachineName($data['name'])
            ->addStates($states)
            ->addTransitions($transitions)
            ->build();
    }

    /**
     * @param  mixed[] $config
     *
     * @return mixed[]
     */
    private function realizeConfig(array $config): array
    {
        $states = [];
        $transitions = [];
        foreach ($config as $name => $state_config) {
            $states[] = $this->factory->createState($name, $state_config);
            if (!is_array($state_config)) {
                continue;
            }
            foreach ($state_config['transitions'] as $key => $transition_config) {
                if (is_string($transition_config)) {
                    $transition_config = [ 'when' => $transition_config ];
                }
                $transitions[] = $this->factory->createTransition($name, $key, $transition_config);
            }
        }
        return [ $states, $transitions ];
    }
}
