<?php

declare(strict_types=1);

namespace Spiral\Domain\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation()
 * @Annotation\Target({"METHOD"})
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD)]
#[NamedArgumentConstructor()]
class Pipeline
{
    /**
     * @Annotation\Attribute(name="pipeline", type="array", required=true)
     * @var array
     */
    public $pipeline;

    /**
     * @Annotation\Attribute(name="skipNext", type="bool")
     * @var bool
     */
    public $skipNext;

    public function __construct(array $pipeline = [], bool $skipNext = false)
    {
        $this->pipeline = $pipeline;
        $this->skipNext = $skipNext;
    }
}
