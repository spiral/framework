<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Container;

use Psr\Container\ContainerExceptionInterface;
use Spiral\Core\Exception\RuntimeException;

/**
 * Something inside container.
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        protected array &$trace = []
    ) {
        parent::__construct($this->addTrace($message), $code, $previous);

        $trace = [];
    }

    protected function addTrace(string $message): string
    {
        $result = [];
        $result[] = $message;

        if ($this->trace !== []) {
            $result[] = 'Container stack trace:';

            foreach ($this->trace as $item) {
                $result[] = $item;
            }
        }

        return \implode(PHP_EOL, $result);
    }
}
