<?php

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src');

//$versions = GitVersionCollection::create(__DIR__)->add('master', 'master branch')->addFromTags('*');

return new Sami($iterator, [
    'title'                => 'workflux - api doc',
    'default_opened_level' => 2,
    'build_dir'            => __DIR__ . '/build/docs/sami/api/%version%',
    'cache_dir'            => __DIR__ . '/build/cache/sami/%version%',
//    'versions'             => $versions,
//    'theme'                => 'github',
//    'template_dirs'        => [__DIR__ . '/vendor/devedge/sami-github'],
]);
