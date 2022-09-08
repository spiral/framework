<?php

declare(strict_types=1);

namespace Spiral\Views\Event;

final class ViewNotFound
{
    public function __construct(
        public readonly string $path,
    ) {
    }
}
