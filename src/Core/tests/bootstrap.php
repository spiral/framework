<?php

declare(strict_types=1);

namespace Spiral\Framework;

require_once __DIR__ . '/../vendor/autoload.php';

if (!\enum_exists(Spiral::class)) {
    enum Spiral: string {
        case HttpRequest = 'http-request';
    }
}
