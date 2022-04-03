<?php

declare(strict_types=1);

namespace Spiral\Stempler\Exception\Traits;

use Spiral\Stempler\Parser\Context;

/**
 * To be used on Exceptions.
 */
trait ContextTrait
{
    public function __construct(
        string $message,
        private Context $context,
        \Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function setLocation(string $filename, int $line): void
    {
        $this->file = $filename;
        $this->line = $line;
    }
}
