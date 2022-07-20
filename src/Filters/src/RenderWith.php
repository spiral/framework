<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({ "CLASS" })
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class RenderWith
{
    /**
     * @var class-string<RenderErrors>
     */
    private string $rendererFqcn;

    /**
     * @param class-string<RenderErrors> $rendererFqcn
     */
    public function __construct(string $rendererFqcn)
    {
        $this->rendererFqcn = $rendererFqcn;
    }

    /**
     * @return class-string<RenderErrors>
     */
    public function getRenderer(): string
    {
        return $this->rendererFqcn;
    }
}
