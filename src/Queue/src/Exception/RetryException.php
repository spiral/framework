<?php

declare(strict_types=1);

namespace Spiral\Queue\Exception;

use Spiral\Queue\OptionsInterface;

final class RetryException extends StateException
{
    public function __construct(
        string $reason = '',
        private readonly ?OptionsInterface $options = null
    ) {
        parent::__construct($reason);
    }

    public function getOptions(): ?OptionsInterface
    {
        return $this->options;
    }
}
