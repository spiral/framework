<?php

declare(strict_types=1);

namespace Framework\Framework;

use Spiral\App\TestApp;
use Spiral\Tests\Framework\BaseTest;

final class KernelTest extends BaseTest
{
    public function testAppBootingCallbacks()
    {
        $kernel = $this->createAppInstance();

        $kernel->appBooting(static function (TestApp $core) {
            $core->getContainer()->bind('abc', 'foo');
        });

        $kernel->appBooting(static function (TestApp $core) {
            $core->getContainer()->bind('bcd', 'foo');
        });

        $kernel->appBooted(static function (TestApp $core) {
            $core->getContainer()->bind('cde', 'foo');
        });

        $kernel->appBooted(static function (TestApp $core) {
            $core->getContainer()->bind('def', 'foo');
        });

        $kernel->run();

        $this->assertTrue($kernel->getContainer()->has('abc'));
        $this->assertTrue($kernel->getContainer()->has('bcd'));
        $this->assertTrue($kernel->getContainer()->has('cde'));
        $this->assertTrue($kernel->getContainer()->has('def'));
    }
}
