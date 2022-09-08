<?php

declare(strict_types=1);

namespace Spiral\Tests\Files;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected const FIXTURE_DIRECTORY = __DIR__ . '/fixtures/';
}
