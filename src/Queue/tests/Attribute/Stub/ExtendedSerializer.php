<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute\Stub;

use Doctrine\Common\Annotations\Annotation\Target;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Queue\Attribute\Serializer;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class ExtendedSerializer extends Serializer
{
    public function __construct()
    {
        parent::__construct('foo');
    }
}
