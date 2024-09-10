<?php

declare(strict_types=1);

use Spiral\Core\Container\Autowire;

return [
    'id'       => 'hello world',
    'autowire' => new Autowire('something')
];
