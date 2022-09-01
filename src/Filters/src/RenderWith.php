<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({ "CLASS" })
 * @psalm-type TClass = class-string<RenderErrorsInterface>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class RenderWith
{
    /** @var TClass */
    private string $class;

    /**
     * @param TClass $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @return TClass
     */
    public function getRenderer(): string
    {
        return $this->class;
    }
}
