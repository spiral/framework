<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Attribute\Input\Input;
use Spiral\Filters\Model\Filter;

class LogoutFilter extends Filter
{
    #[Input]
    public readonly string $token;
}
