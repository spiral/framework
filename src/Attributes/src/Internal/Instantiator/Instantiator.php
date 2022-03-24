<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

use Spiral\Attributes\Internal\ContextRenderer;

abstract class Instantiator implements InstantiatorInterface
{
    /**
     * @var string
     */
    private const CONSTRUCTOR_NAME = '__construct';

    public function __construct(
        protected ContextRenderer $renderer = new ContextRenderer()
    ) {
    }

    protected function getConstructor(\ReflectionClass $class): ?\ReflectionMethod
    {
        if ($class->hasMethod(self::CONSTRUCTOR_NAME)) {
            return $class->getMethod(self::CONSTRUCTOR_NAME);
        }

        $constructor = $this->getTraitConstructors($class);
        if ($constructor !== null) {
            return $constructor;
        }

        if ($parent = $class->getParentClass()) {
            return $this->getConstructor($parent);
        }

        return null;
    }

    private function getTraitConstructors(\ReflectionClass $class): ?\ReflectionMethod
    {
        foreach ($class->getTraits() as $trait) {
            if (($constructor = $this->getConstructor($trait)) !== null) {
                return $constructor;
            }

            if (($constructor = $this->getTraitConstructors($trait)) !== null) {
                return $constructor;
            }
        }

        return null;
    }
}
