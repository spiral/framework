<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use Spiral\Framework\Spiral;
use Spiral\Session\SessionFactory;
use Spiral\Session\SessionFactoryInterface;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\BaseTestCase;

final class SessionBootloaderTest extends BaseTestCase
{
    #[TestScope(Spiral::Http)]
    public function testSessionFactoryInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(SessionFactoryInterface::class, SessionFactory::class);
    }

    public function testSessionFactoryInterfaceBindingInRootScope(): void
    {
        $this->assertContainerBoundAsSingleton(SessionFactoryInterface::class, SessionFactoryInterface::class);
    }
}
