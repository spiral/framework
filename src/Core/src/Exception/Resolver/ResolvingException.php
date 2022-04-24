<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Traits\ClosureRendererTrait;

abstract class ResolvingException extends ContainerException
{
    use ClosureRendererTrait;

    protected function RenderFunctionAndParameter(
        \ReflectionFunctionAbstract $reflection,
        string $pattern
    ): string {
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

        return \sprintf($pattern, $method, $fileAndLine);
    }
}
