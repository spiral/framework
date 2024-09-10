<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Spiral\Tests\Tokenizer\Classes\Listeners\CommandInterfaceListener;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithAllTargets;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithClass;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithExtends;
use Spiral\Tests\Tokenizer\Classes\Listeners\ControllerListener;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithAttributeOnClass;
use Spiral\Tests\Tokenizer\Classes\Listeners\ConstantListener;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithAttributeOnConstant;
use Spiral\Tests\Tokenizer\Classes\Listeners\RouteListener;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithAttributeOnMethod;
use Spiral\Tests\Tokenizer\Classes\Listeners\ParameterListener;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithAttributeOnParameter;
use Spiral\Tests\Tokenizer\Interfaces\Targets\InterfaceWithoutTargets;
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
            CommandInterfaceListener::class,
            [
                InterfaceWithAllTargets::class,
                InterfaceWithClass::class,
                InterfaceWithExtends::class,
            ],
        ];

        yield 'attribute-on-class' => [
            ControllerListener::class,
            [
                InterfaceWithAllTargets::class,
                InterfaceWithAttributeOnClass::class,
            ],
        ];

        yield 'attribute-on-constant' => [
            ConstantListener::class,
            [
                InterfaceWithAllTargets::class,
                InterfaceWithAttributeOnConstant::class,
            ],
        ];

        yield 'attribute-on-method' => [
            RouteListener::class,
            [
                InterfaceWithAllTargets::class,
                InterfaceWithAttributeOnMethod::class,
            ],
        ];

        yield 'attribute-on-parameter' => [
            ParameterListener::class,
            [
                InterfaceWithAllTargets::class,
                InterfaceWithAttributeOnParameter::class,
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
                InterfaceWithAllTargets::class,
                InterfaceWithAttributeOnClass::class,
                InterfaceWithAttributeOnConstant::class,
                InterfaceWithAttributeOnMethod::class,
                InterfaceWithAttributeOnParameter::class,
                InterfaceWithClass::class,
                InterfaceWithExtends::class,
                InterfaceWithoutTargets::class,
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
