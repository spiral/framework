<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Security;

use Spiral\Tests\Framework\BaseTest;
use Spiral\Validation\Config\ValidatorConfig;

/**
 * @coversDefaultClass \Spiral\Bootloader\Security\ValidationBootloader
 */
class ValidationBootloaderTest extends BaseTest
{
    /**
     * @dataProvider dataHasCheckerByDefault
     */
    public function testHasCheckerByDefault(string $checkerName): void
    {
        $app = $this->makeApp();
        $config = $app->get(ValidatorConfig::class);
        self::assertNotEmpty($config->hasChecker($checkerName));
    }

    public function dataHasCheckerByDefault(): iterable
    {
        yield ['type'];
        yield ['number'];
        yield ['mixed'];
        yield ['string'];
        yield ['file'];
        yield ['image'];
        yield ['datetime'];
        yield ['entity'];
        yield ['array'];
    }
}
