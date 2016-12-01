<?php

namespace Workflux\Tests;

use PHPUnit_Framework_TestCase;
use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Workflux\Param\Settings;
use Workflux\State\State;
use Workflux\State\StateInterface;

class TestCase extends PHPUnit_Framework_TestCase
{
    public function createState(
        $name,
        $implementor = State::ClASS,
        $settings = null,
        $input_schema = null,
        $output_schema = null
    ): StateInterface {
        return new $implementor($name, ...$this->getDefaultStateArgs($settings, $input_schema, $output_schema));
    }

    public function getDefaultStateArgs($settings = null, $input_schema = null, $output_schema = null): array
    {
        return [
            $settings ?: new Settings,
            $input_schema ?: new Schema('input_schema', [ 'type' => 'assoc', 'properties' => [] ], new Factory),
            $output_schema ?: new Schema('output_schema', [ 'type' => 'assoc', 'properties' => [] ], new Factory)
        ];
    }
}
