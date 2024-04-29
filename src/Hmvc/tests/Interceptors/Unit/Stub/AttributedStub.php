<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Stub;

use Spiral\Interceptors\Context\AttributedInterface;
use Spiral\Interceptors\Context\AttributedTrait;

final class AttributedStub implements AttributedInterface
{
    use AttributedTrait;
}
