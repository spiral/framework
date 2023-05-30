<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Tag\Stub;

use Spiral\Core\Attribute\Tag;

#[Tag('logger')]
final class FileLogger implements LoggerInterface
{
}
