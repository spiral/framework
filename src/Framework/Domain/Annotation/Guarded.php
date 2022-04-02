<?php

declare(strict_types=1);

namespace Spiral\Domain\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Enum;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"METHOD", "CLASS"})
 * @Attributes({
 *     @Attribute("permission", type="string"),
 *     @Attribute("else", type="string"),
 *     @Attribute("errorMessage", type="string")
 * })
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class Guarded
{
    /**
     * @param string|null $errorMessage Error message in case of error.
     */
    public function __construct(
        public readonly ?string $permission = null,
        /** @Enum({"notFound","unauthorized","forbidden","badAction","error"}) */
        public readonly string $else = 'forbidden',
        public readonly ?string $errorMessage = null
    ) {
    }
}
