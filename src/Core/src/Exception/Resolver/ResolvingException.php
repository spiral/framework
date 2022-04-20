<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

use Spiral\Core\Exception\Traits\ClosureRendererTrait;

abstract class ResolvingException extends \InvalidArgumentException
{
    use ClosureRendererTrait;

    protected const EXCEPTION_MESSAGE = 'Something is wrong with argument `%s` when calling `%s` %s.';

    public function __construct(\ReflectionFunctionAbstract $reflection, string $parameter)
    {
        $function = $reflection->getName();
        /** @var class-string|null $class */
        $class = $reflection->class ?? null;

        $method = match (true) {
            $class !== null => "{$class}::{$function}",
            $reflection->isClosure() => $this->renderClosureSignature($reflection),
            default => $function,
        };

        $fileName = $reflection->getFileName();
        $line = $reflection->getStartLine();

        $fileAndLine = '';
        if (!empty($fileName)) {
            $fileAndLine = "in \"$fileName\" at line $line";
        }

        parent::__construct(\sprintf((string)static::EXCEPTION_MESSAGE, $parameter, $method, $fileAndLine));
    }
}
