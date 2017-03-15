<?php

namespace Workflux\Builder;

use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;
use Workflux\Builder\Factory;
use Workflux\Builder\StateMachineBuilderInterface;
use Workflux\Error\ConfigError;
use Workflux\StateMachine;
use Workflux\StateMachineInterface;

final class ArrayStateMachineBuilder implements StateMachineBuilderInterface
{
    /**
     * @var mixed[] $config
     */
    private $config;

    /**
     * @var FactoryInterface $factory
     */
    private $factory;

    /**
     * @var StateMachineSchema $schema
     */
    private $schema;

    /**
     * @param array $config
     * @param FactoryInterface|null $factory
     */
    public function __construct(array $config, FactoryInterface $factory = null)
    {
        $this->config = $config;
        $this->schema = new StateMachineSchema;
        $this->factory = $factory ?? new Factory;
    }

    /**
     * @return StateMachineInterface
     */
    public function build(): StateMachineInterface
    {
        $data = $this->config;
        $result = (new StateMachineSchema)->validate($data);
        if ($result instanceof Error) {
            throw new ConfigError('Invalid statemachine configuration given: '.print_r($result->unwrap(), true));
        }
        list($states, $transitions) = $this->realizeConfig($data['states']);
        $state_machine_class = Maybe::unit($this->config)->class->get() ?? StateMachine::CLASS;
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
