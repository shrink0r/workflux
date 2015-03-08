<?php

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()->files()->name('*.php')->in(__DIR__ . DIRECTORY_SEPARATOR . 'src');

//$versions = GitVersionCollection::create(__DIR__)->add('master', 'master branch')->addFromTags('*');

return new Sami($iterator, array(
//    'versions'             => $versions,
    'title'                => 'Workflux API',
    'build_dir'            => __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/docs/api/html/%version%'),
    'cache_dir'            => __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/build/cache/html/%version%'),
    'default_opened_level' => 2,
));
