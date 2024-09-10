<?php

declare(strict_types=1);

namespace Spiral\App\ViewEngine;

use Spiral\Views\ViewInterface;
use Spiral\Views\ViewSource;

class View implements ViewInterface
{
    public function __construct(private readonly ViewSource $source)
    {
    }

    public function render(array $data = []): string
    {
        return $this->source->getCode();
    }
}
