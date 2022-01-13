<?php

declare(strict_types=1);

namespace Spiral\Domain\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Annotation\Target({"METHOD"})
 */
#[\Attribute(\Attribute::TARGET_METHOD), NamedArgumentConstructor]
class Pipeline
{
    /**
     * @Annotation\Attribute(name="pipeline", type="array", required=true)
     * @var array
     */
    public $pipeline = [];

    /**
     * @Annotation\Attribute(name="skipNext", type="bool")
     * @var bool
     */
    public $skipNext = false;

    public function __construct(array $pipeline = [], bool $skipNext = false)
    {
        $this->pipeline = $pipeline;
        $this->skipNext = $skipNext;
    }
}
