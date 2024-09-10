<?php

declare(strict_types=1);

use Spiral\Core\ContainerScope;
use Spiral\Tests\Config\Value;

return [
    'value' => ContainerScope::getContainer()->get(Value::class)->getValue()
];
