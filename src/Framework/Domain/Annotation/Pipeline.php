<?php

declare(strict_types=1);

namespace Spiral\Domain\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Annotation\Target({"METHOD"})
 * @Attributes({
 *     @Attribute("pipeline", required=true, type="array"),
 *     @Attribute("skipNext", type="bool")
 * })
 */
#[\Attribute(\Attribute::TARGET_METHOD), NamedArgumentConstructor]
class Pipeline
{
    public function __construct(
        public readonly array $pipeline = [],
        public readonly bool $skipNext = false
    ) {
    }
}
