<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

use Spiral\Attributes\Internal\ContextRenderer;

abstract class Instantiator implements InstantiatorInterface
{
    /**
     * @var string
     */
    private const CONSTRUCTOR_NAME = '__construct';

    /**
     * @var ContextRenderer
     */
    protected $renderer;

    /**
     * @param ContextRenderer|null $renderer
     */
    public function __construct(ContextRenderer $renderer = null)
    {
        $this->renderer = $renderer ?? new ContextRenderer();
    }

    /**
     * @param \ReflectionClass $class
     * @return \ReflectionMethod|null
     */
    protected function getConstructor(\ReflectionClass $class): ?\ReflectionMethod
    {
        if ($class->hasMethod(self::CONSTRUCTOR_NAME)) {
            return $class->getMethod(self::CONSTRUCTOR_NAME);
        }

        if ($constructor = $this->getTraitConstructors($class)) {
            return $constructor;
        }

        if ($parent = $class->getParentClass()) {
            return $this->getConstructor($parent);
        }

        return null;
    }

    /**
     * @param \ReflectionClass $class
     * @return \ReflectionMethod|null
     */
    private function getTraitConstructors(\ReflectionClass $class): ?\ReflectionMethod
    {
        foreach ($class->getTraits() as $trait) {
            if ($constructor = $this->getConstructor($trait)) {
                return $constructor;
            }

            if ($constructor = $this->getTraitConstructors($trait)) {
                return $constructor;
            }
        }

        return null;
    }
}
