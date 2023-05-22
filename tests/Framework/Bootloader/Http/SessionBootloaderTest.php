<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use Spiral\Session\SessionFactory;
use Spiral\Session\SessionFactoryInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class SessionBootloaderTest extends BaseTestCase
{
    public function testSessionFactoryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(SessionFactoryInterface::class, SessionFactory::class);
    }
}
