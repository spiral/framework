<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Tests\Tokenizer\Classes\Listeners;
use Spiral\Tests\Tokenizer\Interfaces\Targets;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\EnumsInterface;
use Spiral\Tokenizer\InterfacesInterface;
use Spiral\Tokenizer\Listener\ClassLocatorByTarget;
use Spiral\Tokenizer\Listener\EnumLocatorByTarget;
use Spiral\Tokenizer\Listener\InterfaceLocatorByTarget;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedEnumsInterface;
use Spiral\Tokenizer\ScopedInterfacesInterface;

final class InterfaceLocatorByTargetTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private InterfaceLocatorByTarget $locator;
    private InterfacesInterface|m\LegacyMockInterface|m\MockInterface $interfaces;
    private ScopedInterfacesInterface|m\LegacyMockInterface|m\MockInterface $scopedInterfaces;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = new InterfaceLocatorByTarget(
            $this->interfaces = m::mock(InterfacesInterface::class),
            $this->scopedInterfaces = m::mock(ScopedInterfacesInterface::class),
        );
    }

    public static function provideGetEnums(): \Generator
    {
        yield 'class' => [
            Listeners\CommandInterfaceListener::class,
            [
                Targets\InterfaceWithAllTargets::class,
                Targets\InterfaceWithClass::class,
                Targets\InterfaceWithExtends::class,
            ],
        ];

        yield 'attribute-on-class' => [
            Listeners\ControllerListener::class,
            [
                Targets\InterfaceWithAllTargets::class,
                Targets\InterfaceWithAttributeOnClass::class,
            ],
        ];

        yield 'attribute-on-constant' => [
            Listeners\ConstantListener::class,
            [
                Targets\InterfaceWithAllTargets::class,
                Targets\InterfaceWithAttributeOnConstant::class,
            ],
        ];

        yield 'attribute-on-method' => [
            Listeners\RouteListener::class,
            [
                Targets\InterfaceWithAllTargets::class,
                Targets\InterfaceWithAttributeOnMethod::class,
            ],
        ];

        yield 'attribute-on-parameter' => [
            Listeners\ParameterListener::class,
            [
                Targets\InterfaceWithAllTargets::class,
                Targets\InterfaceWithAttributeOnParameter::class,
            ],
        ];
    }

    #[DataProvider('provideGetEnums')]
    public function testGetEnums(
        string $listener,
        array $expected,
    ): void {
        $interfaces = \array_map(
            fn (string $class) => new \ReflectionClass($class),
            [
                Targets\InterfaceWithAllTargets::class,
                Targets\InterfaceWithAttributeOnClass::class,
                Targets\InterfaceWithAttributeOnConstant::class,
                Targets\InterfaceWithAttributeOnMethod::class,
                Targets\InterfaceWithAttributeOnParameter::class,
                Targets\InterfaceWithClass::class,
                Targets\InterfaceWithExtends::class,
                Targets\InterfaceWithoutTargets::class,
            ],
        );

        $attr = new \ReflectionClass($listener);
        $attr = $attr->getAttributes()[0];
        $attribute = $attr->newInstance();

        if ($attribute->getScope() === null) {
            $this->interfaces
                ->shouldReceive('getInterfaces')
                ->andReturn($interfaces);
        } else {
            $this->scopedInterfaces
                ->shouldReceive('getScopedInterfaces')
                ->with($attribute->getScope())
                ->andReturn($interfaces);
        }

        $interfaces = $this->locator->getInterfaces(
            $attribute
        );

        $this->assertSame($expected, $interfaces);
    }
}
