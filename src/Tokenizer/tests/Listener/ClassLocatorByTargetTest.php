<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Spiral\Tests\Tokenizer\Classes\Listeners\CommandInterfaceListener;
use Spiral\Tests\Tokenizer\Classes\Targets\ConsoleCommand;
use Spiral\Tests\Tokenizer\Classes\Targets\ConsoleCommandInterface;
use Spiral\Tests\Tokenizer\Classes\Targets\ConsoleCommandWithExtend;
use Spiral\Tests\Tokenizer\Classes\Listeners\TraitListener;
use Spiral\Tests\Tokenizer\Classes\Targets\ClassWithAttributeOnConstant;
use Spiral\Tests\Tokenizer\Classes\Targets\ClassWithTrait;
use Spiral\Tests\Tokenizer\Classes\Listeners\ControllerListener;
use Spiral\Tests\Tokenizer\Classes\Targets\ClassWithAttributeOnClass;
use Spiral\Tests\Tokenizer\Classes\Listeners\CommandListener;
use Spiral\Tests\Tokenizer\Classes\Targets\Filter;
use Spiral\Tests\Tokenizer\Classes\Targets\ClassWithAttributeOnProperty;
use Spiral\Tests\Tokenizer\Classes\Listeners\ConstantListener;
use Spiral\Tests\Tokenizer\Classes\Listeners\ParameterListener;
use Spiral\Tests\Tokenizer\Classes\Targets\HomeController;
use Spiral\Tests\Tokenizer\Classes\Targets\ClassWithAttributeOnParameter;
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
            CommandInterfaceListener::class,
            [
                ConsoleCommand::class,
                ConsoleCommandInterface::class,
                ConsoleCommandWithExtend::class,
            ],
        ];

        yield 'trait' => [
            TraitListener::class,
            [
                ClassWithAttributeOnConstant::class,
                ClassWithTrait::class,
            ],
        ];

        yield 'attribute-on-class' => [
            ControllerListener::class,
            [
                ConsoleCommand::class,
                ClassWithAttributeOnClass::class,
            ],
        ];

        yield 'attribute-on-property' => [
            CommandListener::class,
            [
                Filter::class,
                ClassWithAttributeOnProperty::class,
            ],
        ];

        yield 'attribute-on-constant' => [
            ConstantListener::class,
            [
                ConsoleCommand::class,
                ClassWithAttributeOnConstant::class,
            ],
        ];

        yield 'attribute-on-parameter' => [
            ParameterListener::class,
            [
                HomeController::class,
                ClassWithAttributeOnParameter::class,
            ],
        ];
    }

    #[DataProvider('provideGetClasses')]
    public function testGetClasses(
        string $listener,
        array $expected,
    ): void {
        $classes = \array_map(
            fn (string $class) => new \ReflectionClass($class),
            [
                ConsoleCommand::class,
                Filter::class,
                ConsoleCommandInterface::class,
                HomeController::class,
                ConsoleCommandWithExtend::class,
                ClassWithAttributeOnClass::class,
                ClassWithAttributeOnProperty::class,
                ClassWithAttributeOnParameter::class,
                ClassWithAttributeOnConstant::class,
                ClassWithTrait::class,
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
