<?php

declare(strict_types=1);

namespace Spiral\Attributes;

use Doctrine\Common\Annotations\Reader;
use Spiral\Attributes\Internal\Decorator;
use Spiral\Attributes\Internal\DoctrineAnnotationReader;

final class AnnotationReader extends Decorator
{
    public function __construct(Reader $reader = null)
    {
        parent::__construct(new DoctrineAnnotationReader($reader));
    }
}
