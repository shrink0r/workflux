<?php

namespace Workflux\Builder;

use Ds\Map;
use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Factory as PhpSchemaFactory;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Workflux\Error\ConfigError;
use Workflux\Error\MissingImplementation;
use Workflux\Param\Settings;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\InteractiveState;
use Workflux\State\State;
use Workflux\State\StateInterface;
use Workflux\State\Validator;
use Workflux\State\ValidatorInterface;
use Workflux\Transition\ExpressionConstraint;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionInterface;

final class Factory implements FactoryInterface
{
    const SUFFIX_IN = '-input_schema';

    const SUFFIX_OUT = '-output_schema';

    /**
     * @var mixed[] $default_classes
     */
    private static $default_classes = [
        'initial' => InitialState::CLASS,
        'interactive' => InteractiveState::CLASS,
        'state' => State::CLASS,
        'final' => FinalState::CLASS,
        'transition' => Transition::CLASS
    ];

    /**
     * @var mixed[] $default_validation_schema
     */
    private static $default_validation_schema = [ ':any_name:' => [ 'type' => 'any' ] ];

    /**
     * @var ExpressionLanguage $expression_engine
     */
    private $expression_engine;

    /**
     * @param array $defaultss_map
     * @param ExpressionLanguage|null $expression_engine
     */
    public function __construct(array $class_map = [], ExpressionLanguage $expression_engine = null)
    {
        $this->expression_engine = $expression_engine ?? new ExpressionLanguage;
        $this->class_map = new Map(array_merge(self::$default_classes, $class_map));
    }

    /**
     * @param string $name
     * @param mixed[]|null $state
     *
     * @return StateInterface
     */
    public function createState(string $name, array $state = null): StateInterface
    {
        $state = Maybe::unit($state);
        $state_implementor = $this->resolveStateImplementor($state);
        $settings = $state->settings->get() ?? [];
        $settings['_output'] = $state->output->get() ?? [];
        $state_instance = new $state_implementor(
            $name,
            new Settings($settings),
            $this->createValidator($name, $state),
            $this->expression_engine
        );
        if ($state->final->get() && !$state_instance->isFinal()) {
            throw new ConfigError("Trying to provide custom state that isn't final but marked as final in config.");
        }
        if ($state->initial->get() && !$state_instance->isInitial()) {
            throw new ConfigError("Trying to provide custom state that isn't initial but marked as initial in config.");
        }
        if ($state->interactive->get() && !$state_instance->isInteractive()) {
            throw new ConfigError(
                "Trying to provide custom state that isn't interactive but marked as interactive in config."
            );
        }
        return $state_instance;
    }

    /**
     * @param string $from
     * @param string $to
     * @param  mixed[]|null $transition
     *
     * @return TransitionInterface
     */
    public function createTransition(string $from, string $to, array $config = null): TransitionInterface
    {
        $transition = Maybe::unit($config);
        if (is_string($transition->when->get())) {
            $config['when'] = [ $transition->when->get() ];
        }
        $implementor = $transition->class->get() ?? $this->class_map->get('transition');
        if (!in_array(TransitionInterface::CLASS, class_implements($implementor))) {
            throw new MissingImplementation(
                'Trying to create transition without implementing required '.TransitionInterface::CLASS
            );
        }
        $constraints = [];
        foreach (Maybe::unit($config)->when->get() ?? [] as $expression) {
            if (!is_string($expression)) {
                continue;
            }
            $constraints[] = new ExpressionConstraint($expression, $this->expression_engine);
        }
        $settings = new Settings(Maybe::unit($config)->settings->get() ?? []);
        return new $implementor($from, $to, $settings, $constraints);
    }

    /**
     * @param Maybe $state
     *
     * @return string
     */
    private function resolveStateImplementor(Maybe $state): string
    {
        switch (true) {
            case $state->initial->get():
                $state_implementor = $this->class_map->get('initial');
                break;
            case $state->final->get() === true || $state->get() === null: // cast null to final-state by convention
                $state_implementor = $this->class_map->get('final');
                break;
            case $state->interactive->get():
                $state_implementor = $this->class_map->get('interactive');
                break;
            default:
                $state_implementor = $this->class_map->get('state');
        }
        $state_implementor = $state->class->get() ?? $state_implementor;
        if (!in_array(StateInterface::CLASS, class_implements($state_implementor))) {
            throw new MissingImplementation(
                'Trying to use a custom-state that does not implement required '.StateInterface::CLASS
            );
        }
        return $state_implementor;
    }

    /**
     * @param string $name
     * @param  Maybe $state
     *
     * @return ValidatorInterface
     */
    private function createValidator(string $name, Maybe $state): ValidatorInterface
    {
        return new Validator(
            $this->createValidationSchema(
                $name.self::SUFFIX_IN,
                $state->input_schema->get() ?? self::$default_validation_schema
            ),
            $this->createValidationSchema(
                $name.self::SUFFIX_OUT,
                $state->output_schema->get() ?? self::$default_validation_schema
            )
        );
    }

    /**
     * @param string $name
     * @param array $schema_definition
     *
     * @return SchemaInterface
     */
    private function createValidationSchema(string $name, array $schema_definition): SchemaInterface
    {
        return new Schema($name, [ 'type' => 'assoc', 'properties' => $schema_definition ], new PhpSchemaFactory);
    }
}
