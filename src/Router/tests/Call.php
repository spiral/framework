<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

class Call
{
    public function __invoke()
    {
        return 'invoked';
    }
}
