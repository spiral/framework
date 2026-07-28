<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

final class EnumService
{
    public function __construct(public EnumObject $enum) {}
}
