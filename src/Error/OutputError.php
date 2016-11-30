<?php

namespace Workflux\Error;

use Workflux\Error\WorkfluxError;

class OutputError extends WorkfluxError
{
    private $validation_errors;

    public function __construct(array $validation_errors, $msg = '')
    {
        $this->validation_errors = $validation_errors;

        parent::__construct($msg);
    }

    public function getValidationErrors()
    {
        return $this->validation_errors;
    }

    public function __toString()
    {
        $errors = [ $this->getMessage() ];
        foreach ($this->validation_errors as $prop_name => $errors) {
            $errors[] = $prop_name.": ".implode(', ', $errors);
        }
        return implode("\n", $errors);
    }
}
