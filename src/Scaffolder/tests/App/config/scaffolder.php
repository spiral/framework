<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 * @see     \Spiral\Scaffolder\Config\ScaffolderConfig
 */

declare(strict_types=1);

return [
    'header'       => [
        '{project-name}',
        '',
        '@author {author-name}',
    ],
    'directory' => directory('app'),
    'namespace' => 'Spiral\\Tests\\Scaffolder\\App',
];
