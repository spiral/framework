<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\Exception\SetterException;

#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
class CastingErrorMessage
{
    protected ?\Closure $callback = null;

    /**
     * @param callable(SetterException $exception, mixed $value): string $callback
     */
    public function __construct(
        protected ?string $message = null,
        ?callable $callback = null
    ) {
        if ($callback !== null) {
            $this->callback = $callback(...);
        }
    }

    public function getMessage(SetterException $exception, mixed $value = null): ?string
    {
        if ($this->callback instanceof \Closure) {
            return ($this->callback)($exception, $value);
        }

        return $this->message;
    }
}
