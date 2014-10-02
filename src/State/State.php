<?php

namespace Workflux\State;

use Workflux\Error;

class State implements IState
{
    protected $name;

    protected $type;

    public function __construct($name, $type = self::TYPE_ACTIVE)
    {
        $this->assertType($type);

        $this->name = $name;
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isInitial()
    {
        return $this->type === self::TYPE_INITIAL;
    }

    public function isActive()
    {
        return $this->type === self::TYPE_ACTIVE;
    }

    public function isFinal()
    {
        return $this->type === self::TYPE_FINAL;
    }

    protected function assertType($state_type)
    {
        $allowed_types = [ self::TYPE_INITIAL, self::TYPE_ACTIVE, self::TYPE_FINAL ];

        if (!in_array($state_type, $allowed_types)) {
            throw new Error(
                sprintf(
                    'Invalid state type "%s" given.' .
                    ' Only the types %s are permitted.',
                    $state_type,
                    implode(', ', $allowed_types)
                )
            );
        }
    }
}
