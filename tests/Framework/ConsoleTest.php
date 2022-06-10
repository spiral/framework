<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

abstract class ConsoleTest extends BaseTest
{
    public function tearDown(): void
    {
        parent::tearDown();

        $this->cleanUpRuntimeDirectory();
    }
}
