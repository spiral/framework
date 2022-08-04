<?php

declare(strict_types=1);

namespace Spiral\Queue\Exception;

final class FailException extends StateException
{
    public function __construct(string $reason = '')
    {
        parent::__construct($reason);
    }
}
