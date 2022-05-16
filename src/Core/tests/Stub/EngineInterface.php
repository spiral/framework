<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Stub;

/**
 * EngineInterface defines car engine interface
 */
interface EngineInterface
{
    public function getName(): string;

    public function getPower(): int;
}
