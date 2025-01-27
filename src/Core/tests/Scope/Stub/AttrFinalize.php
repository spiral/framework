<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

use Spiral\Core\Attribute\Finalize;

#[Finalize(method: 'finalize')] // always method of the class
final class AttrFinalize
{
    public bool $finalized = false;
    public ?LoggerInterface $logger = null;
    public bool $throwException = false;

    public function finalize(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
        $this->finalized = true;
        if ($this->throwException) {
            throw new \RuntimeException('Test exception from finalize method.');
        }
    }
}
