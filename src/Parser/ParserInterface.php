<?php

namespace Workflux\Parser;

interface ParserInterface
{
    public function parse($payload);
}
