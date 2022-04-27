<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

use Spiral\Core\Exception\Traits\ClosureRendererTrait;

final class PositionalArgumentException extends ValidationException
{
    use ClosureRendererTrait;

    public function __construct(
        \ReflectionFunctionAbstract $reflection,
        private readonly int $position
    ) {
        $pattern = 'Cannot use positional argument after named argument `%s` %s.';
        parent::__construct($this->renderFunctionAndParameter($reflection, $pattern));
    }

    public function getParameter(): string
    {
        return '#' . $this->position;
    }
}
