<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

abstract class ConsoleTestCase extends BaseTestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        $this->cleanUpRuntimeDirectory();
    }
}
