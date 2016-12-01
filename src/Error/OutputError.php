<?php

namespace Workflux\Error;

use Workflux\Error\WorkfluxError;

class OutputError extends WorkfluxError
{
    /**
     * @var string[] $validation_errors
     */
    private $validation_errors;

    /**
     * @param string[] $validation_errors
     * @param string $msg
     */
    public function __construct(array $validation_errors, $msg = '')
    {
        $this->validation_errors = $validation_errors;

        parent::__construct($msg);
    }

    /**
     * @return string[]
     */
    public function getValidationErrors()
    {
        return $this->validation_errors;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $errors = [ $this->getMessage() ];
        foreach ($this->validation_errors as $prop_name => $errors) {
            $errors[] = $prop_name.": ".implode(', ', $errors);
        }
        return implode("\n", $errors);
    }
}
