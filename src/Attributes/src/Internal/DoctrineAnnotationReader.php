<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Spiral\Attributes\Exception\AttributeException;
use Spiral\Attributes\Exception\InitializationException;
use Spiral\Attributes\Exception\SemanticAttributeException;
use Spiral\Attributes\Exception\SyntaxAttributeException;
use Spiral\Attributes\Reader as BaseReader;

final class DoctrineAnnotationReader extends BaseReader
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
        $result = $this->wrapDoctrineExceptions(function () use ($class) {
            return $this->reader->getClassAnnotations($class);
        });

        return $this->filter($name, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        if ($function instanceof \ReflectionMethod) {
            $result = $this->wrapDoctrineExceptions(function () use ($function) {
                return $this->reader->getMethodAnnotations($function);
            });

            return $this->filter($name, $result);
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        $result = $this->wrapDoctrineExceptions(function () use ($property) {
            return $this->reader->getPropertyAnnotations($property);
        });

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

    private function wrapDoctrineExceptions(\Closure $then): iterable
    {
        try {
            return $then();
        } catch (AnnotationException $e) {
            switch (true) {
                case \str_starts_with($e->getMessage(), '[Syntax Error]'):
                case \str_starts_with($e->getMessage(), '[Type Error]'):
                    $class = SyntaxAttributeException::class;
                    break;

                case \str_starts_with($e->getMessage(), '[Semantical Error]'):
                case \str_starts_with($e->getMessage(), '[Creation Error]'):
                    $class = SemanticAttributeException::class;
                    break;

                default:
                    $class = AttributeException::class;
            }

            throw new $class($e->getMessage(), (int)$e->getCode(), $e);
        }
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
}
