<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

final class NewObjectInParam
{
    public function __construct(
        private object $object = new \stdClass(),
    ) {}
}
