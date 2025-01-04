<?php

declare(strict_types=1);

namespace Framework\Framework;

use Spiral\App\TestApp;
use Spiral\Tests\Framework\BaseTestCase;

final class KernelTest extends BaseTestCase
{
    public function testAppBootingCallbacks(): void
    {
        $kernel = $this->createAppInstance();

        $kernel->appBooting(static function (TestApp $core): void {
            $core->getContainer()->bind('abc', 'foo');
        });

        $kernel->appBooting(static function (TestApp $core): void {
            $core->getContainer()->bind('bcd', 'foo');
        });

        $kernel->appBooted(static function (TestApp $core): void {
            $core->getContainer()->bind('cde', 'foo');
        });

        $kernel->appBooted(static function (TestApp $core): void {
            $core->getContainer()->bind('def', 'foo');
        });

        $kernel->run();

        self::assertTrue($kernel->getContainer()->has('abc'));
        self::assertTrue($kernel->getContainer()->has('bcd'));
        self::assertTrue($kernel->getContainer()->has('cde'));
        self::assertTrue($kernel->getContainer()->has('def'));
    }
}
