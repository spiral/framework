<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Spiral\Attributes\Exception\InitializationException;
use Spiral\Attributes\Reader as BaseReader;

final class AnnotationReader extends BaseReader
{
    /**
     * @var Reader|null
     */
    private $reader;

    /**
     * @param Reader|null $reader
     */
    public function __construct(Reader $reader = null)
    {
        $this->checkAvailability();
        $this->bootAnnotations();

        $this->reader = $reader ?? new DoctrineReader();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        $result = $this->reader->getClassAnnotations($class);

        return $this->filter($name, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        if ($function instanceof \ReflectionMethod) {
            $result = $this->reader->getMethodAnnotations($function);

            return $this->filter($name, $result);
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        $result = $this->reader->getPropertyAnnotations($property);

        return $this->filter($name, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        return [];
    }

    /**
     * @return bool
     */
    protected function isAvailable(): bool
    {
        return \interface_exists(Reader::class);
    }

    /**
     * @return void
     */
    private function bootAnnotations(): void
    {
        // doctrine/annotations ^1.0 compatibility.
        if (\method_exists(AnnotationRegistry::class, 'registerLoader')) {
            AnnotationRegistry::registerLoader('\\class_exists');
        }
    }

    /**
     * @return void
     */
    private function checkAvailability(): void
    {
        if ($this->isAvailable()) {
            return;
        }

        throw new InitializationException('Requires the "doctrine/annotations" package');
    }

    /**
     * @param string|null       $name
     * @param iterable|object[] $annotations
     * @return object[]
     */
    private function filter(?string $name, iterable $annotations): iterable
    {
        if ($name === null) {
            yield from $annotations;

            return;
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $name) {
                yield $annotation;
            }
        }
    }
}
