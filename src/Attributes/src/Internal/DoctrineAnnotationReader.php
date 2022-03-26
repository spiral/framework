<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineReader;
use Doctrine\Common\Annotations\Reader;
use Spiral\Attributes\Exception\AttributeException;
use Spiral\Attributes\Exception\InitializationException;
use Spiral\Attributes\Exception\SemanticAttributeException;
use Spiral\Attributes\Exception\SyntaxAttributeException;
use Spiral\Attributes\Reader as BaseReader;

final class DoctrineAnnotationReader extends BaseReader
{
    private Reader $reader;

    public function __construct(Reader $reader = null)
    {
        $this->checkAvailability();

        $this->reader = $reader ?? new DoctrineReader();
    }

    public function getClassMetadata(\ReflectionClass $class, string $name = null): iterable
    {
        $result = $this->wrapDoctrineExceptions(fn () => $this->reader->getClassAnnotations($class));

        yield from $this->filter($name, $result);

        foreach ($class->getTraits() as $trait) {
            yield from $this->getClassMetadata($trait, $name);
        }
    }

    public function getFunctionMetadata(\ReflectionFunctionAbstract $function, string $name = null): iterable
    {
        if ($function instanceof \ReflectionMethod) {
            $result = $this->wrapDoctrineExceptions(fn () => $this->reader->getMethodAnnotations($function));

            return $this->filter($name, $result);
        }

        return [];
    }

    public function getPropertyMetadata(\ReflectionProperty $property, string $name = null): iterable
    {
        $result = $this->wrapDoctrineExceptions(fn () => $this->reader->getPropertyAnnotations($property));

        return $this->filter($name, $result);
    }

    public function getConstantMetadata(\ReflectionClassConstant $constant, string $name = null): iterable
    {
        return [];
    }

    public function getParameterMetadata(\ReflectionParameter $parameter, string $name = null): iterable
    {
        return [];
    }

    protected function isAvailable(): bool
    {
        return \interface_exists(Reader::class);
    }

    private function wrapDoctrineExceptions(\Closure $then): iterable
    {
        try {
            return $then();
        } catch (AnnotationException $e) {
            $class = match (true) {
                \str_starts_with($e->getMessage(), '[Syntax Error]'),
                \str_starts_with($e->getMessage(), '[Type Error]') => SyntaxAttributeException::class,
                \str_starts_with($e->getMessage(), '[Semantical Error]'),
                \str_starts_with($e->getMessage(), '[Creation Error]') => SemanticAttributeException::class,
                default => AttributeException::class,
            };

            throw new $class($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function checkAvailability(): void
    {
        if ($this->isAvailable()) {
            return;
        }

        throw new InitializationException('Requires the "doctrine/annotations" package');
    }
}
