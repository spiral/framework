<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Tests\Tokenizer\Classes\Listeners;
use Spiral\Tests\Tokenizer\Classes\Targets;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Listener\ClassLocatorByTarget;
use Spiral\Tokenizer\ScopedClassesInterface;

final class ClassLocatorByTargetTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ClassLocatorByTarget $locator;
    private ClassesInterface|m\LegacyMockInterface|m\MockInterface $classes;
    private ScopedClassesInterface|m\LegacyMockInterface|m\MockInterface $scopedClasses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = new ClassLocatorByTarget(
            $this->classes = m::mock(ClassesInterface::class),
            $this->scopedClasses = m::mock(ScopedClassesInterface::class),
        );
    }

    public static function provideGetClasses(): \Generator
    {
        yield 'class' => [
            Listeners\CommandInterfaceListener::class,
            [
                Targets\ConsoleCommand::class,
                Targets\ConsoleCommandInterface::class,
                Targets\ConsoleCommandWithExtend::class,
            ],
        ];

        yield 'trait' => [
            Listeners\TraitListener::class,
            [
                Targets\ClassWithAttributeOnConstant::class,
                Targets\ClassWithTrait::class,
            ],
        ];

        yield 'attribute-on-class' => [
            Listeners\ControllerListener::class,
            [
                Targets\ConsoleCommand::class,
                Targets\ClassWithAttributeOnClass::class,
            ],
        ];

        yield 'attribute-on-property' => [
            Listeners\CommandListener::class,
            [
                Targets\Filter::class,
                Targets\ClassWithAttributeOnProperty::class,
            ],
        ];

        yield 'attribute-on-constant' => [
            Listeners\ConstantListener::class,
            [
                Targets\ConsoleCommand::class,
                Targets\ClassWithAttributeOnConstant::class,
            ],
        ];

        yield 'attribute-on-parameter' => [
            Listeners\ParameterListener::class,
            [
                Targets\HomeController::class,
                Targets\ClassWithAttributeOnParameter::class,
            ],
        ];
    }

    #[DataProvider('provideGetClasses')]
    public function testGetClasses(
        string $listener,
        array $expected,
    ): void {
        $classes = \array_map(
            fn (string $class): \ReflectionClass => new \ReflectionClass($class),
            [
                Targets\ConsoleCommand::class,
                Targets\Filter::class,
                Targets\ConsoleCommandInterface::class,
                Targets\HomeController::class,
                Targets\ConsoleCommandWithExtend::class,
                Targets\ClassWithAttributeOnClass::class,
                Targets\ClassWithAttributeOnProperty::class,
                Targets\ClassWithAttributeOnParameter::class,
                Targets\ClassWithAttributeOnConstant::class,
                Targets\ClassWithTrait::class,
            ],
        );

        $attr = new \ReflectionClass($listener);
        $attr = $attr->getAttributes()[0];
        $attribute = $attr->newInstance();

        if ($attribute->getScope() === null) {
            $this->classes
                ->shouldReceive('getClasses')
                ->andReturn($classes);
        } else {
            $this->scopedClasses
                ->shouldReceive('getScopedClasses')
                ->with($attribute->getScope())
                ->andReturn($classes);
        }

        $classes = $this->locator->getClasses(
            $attribute
        );

        $this->assertSame($expected, $classes);
    }
}
