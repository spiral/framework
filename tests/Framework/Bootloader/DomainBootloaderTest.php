<?php

declare(strict_types=1);

namespace Framework\Bootloader;

use Spiral\App\Interceptor\One;
use Spiral\App\Interceptor\Three;
use Spiral\App\Interceptor\Two;
use Spiral\Bootloader\DomainBootloader;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Core;
use Spiral\Core\InterceptableCore;
use Spiral\Tests\Framework\BaseTestCase;

final class DomainBootloaderTest extends BaseTestCase
{
    public function testDefineInterceptors(): void
    {
        $bootloader = new class extends DomainBootloader {
            protected const INTERCEPTORS = ['foo', 'bar'];
        };

        $this->assertSame(
            ['foo', 'bar'],
            (new \ReflectionMethod($bootloader, 'defineInterceptors'))->invoke($bootloader)
        );
    }

    // public function testDomainCore(): void
    // {
    //     $bootloader = new class extends DomainBootloader {
    //         protected static function defineInterceptors(): array
    //         {
    //             return [
    //                 One::class,
    //                 new Autowire(Two::class),
    //                 new Three()
    //             ];
    //         }
    //     };
    //
    //     /** @var InterceptableCore $core */
    //     $core = (new \ReflectionMethod($bootloader, 'domainCore'))
    //         ->invoke($bootloader, $this->getContainer()->get(Core::class), $this->getContainer());
    //     $pipeline = (new \ReflectionProperty($core, 'pipeline'))->getValue($core);
    //
    //     $this->assertEquals(
    //         [new One(), new Two(), new Three()],
    //         (new \ReflectionProperty($pipeline, 'interceptors'))->getValue($pipeline)
    //     );
    // }
}
