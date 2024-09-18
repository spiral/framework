<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

class EnumServiceWithDefault
{
    public function __construct(public EnumObject $enum = EnumObject::qux)
    {
    }
}
