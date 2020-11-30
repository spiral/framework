<?php

/**
 * This file is part of Attributes package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

class Factory implements InstantiatorInterface
{
    /**
     * @var DoctrineInstantiator
     */
    private $doctrine;

    /**
     * @var NamedArgumentsInstantiator
     */
    private $named;

    /**
     * Factory constructor.
     */
    public function __construct()
    {
        $this->doctrine = new DoctrineInstantiator();
        $this->named = new NamedArgumentsInstantiator();
    }

    /**
     * @param \ReflectionClass $attr
     * @param array $arguments
     * @param string $context
     * @return object
     */
    public function instantiate(\ReflectionClass $attr, array $arguments, string $context): object
    {
        if ($this->isNamedArguments($attr)) {
            return $this->named->instantiate($attr, $arguments, $context);
        }

        return $this->doctrine->instantiate($attr, $arguments, $context);
    }

    /**
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isNamedArguments(\ReflectionClass $class): bool
    {
        return \is_subclass_of($class->getName(), NamedArgumentConstructorAnnotation::class);
    }
}
