<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

enum Visibility: string
{
    case PUBLIC = 'public';
    case PROTECTED = 'protected';
    case PRIVATE = 'private';
}
