<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

use Spiral\Core\Exception\Traits\ClosureRendererTrait;

final class ArgumentResolvingException extends ResolvingException
{
    use ClosureRendererTrait;

    private function __construct(
        private readonly \ReflectionFunctionAbstract $reflection,
        private readonly string $parameter,
        ?string $message = null,
    ) {
        $message ??= "Unable to resolve required argument `{$parameter}` when resolving `%s` %s.";
        parent::__construct($this->renderFunctionAndParameter($reflection, $message));
    }

    public static function createWithParam(\ReflectionFunctionAbstract $reflection, string $parameter): self
    {
        return new self($reflection, $parameter);
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    protected static function createStatic(string $message, ?\Throwable $previous): static
    {
        $previous instanceof self or throw new \InvalidArgumentException(
            \sprintf('Previous exception must be an instance of %s', self::class),
        );
        return new self($previous->reflection, $previous->parameter);
    }
}
