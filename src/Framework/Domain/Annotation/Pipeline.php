<?php

declare(strict_types=1);

namespace Spiral\Domain\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target({"METHOD"})
 */
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
}
