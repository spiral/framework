<?php

declare(strict_types=1);

namespace Spiral\Prototype;

use Spiral\Prototype\Traits\PrototypeTrait;
use Spiral\Tokenizer\ScopedClassesInterface;

final class PrototypeLocator
{
    public function __construct(
        private readonly ScopedClassesInterface $classes
    ) {
    }

    /**
     * Locate all classes requiring de-prototyping.
     *
     * @return \ReflectionClass[]
     */
    public function getTargetClasses(): array
    {
        return $this->classes->getScopedClasses('prototypes', PrototypeTrait::class);
    }
}
