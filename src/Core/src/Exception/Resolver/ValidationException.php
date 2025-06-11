<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

use Spiral\Core\Exception\Traits\ClosureRendererTrait;

abstract class ValidationException extends ResolvingException
{
    use ClosureRendererTrait;

    final private function __construct(
        protected readonly \ReflectionFunctionAbstract $reflection,
        protected readonly string $parameter,
        ?string $message = null,
    ) {
        $message ??= $this->getValidationMessage($reflection, $parameter);
        parent::__construct($this->renderFunctionAndParameter($reflection, $message));
    }

    public static function createWithParam(\ReflectionFunctionAbstract $reflection, string $parameter): static
    {
        return new static($reflection, $parameter);
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    protected static function createStatic(string $message, ?\Throwable $previous): static
    {
        $previous instanceof self or throw new \InvalidArgumentException(
            \sprintf('Previous exception must be an instance of %s.', self::class),
        );
        return new static($previous->reflection, $previous->parameter, $message);
    }

    abstract protected function getValidationMessage(
        \ReflectionFunctionAbstract $reflection,
        string $parameter,
    ): string;
}
