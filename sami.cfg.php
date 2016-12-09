<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src');

return new Sami($iterator, [
    'title'                => 'workflux - api doc',
    'default_opened_level' => 2,
    'build_dir'            => __DIR__ . '/build/docs/sami/api/%version%',
    'cache_dir'            => __DIR__ . '/build/cache/sami/%version%'
]);
