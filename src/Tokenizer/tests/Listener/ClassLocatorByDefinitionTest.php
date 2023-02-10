<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\AttributeReader;
use Spiral\Tests\Tokenizer\Classes\Listeners;
use Spiral\Tests\Tokenizer\Classes\Targets;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Listener\AttributesParser;
use Spiral\Tokenizer\Listener\ClassLocatorByDefinition;
use Spiral\Tokenizer\ScopedClassesInterface;

final class ClassLocatorByDefinitionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ClassLocatorByDefinition $locator;
    private ClassesInterface|m\LegacyMockInterface|m\MockInterface $classes;
    private ScopedClassesInterface|m\LegacyMockInterface|m\MockInterface $scopedClasses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = new ClassLocatorByDefinition(
            new AttributeReader(),
            $this->classes = m::mock(ClassesInterface::class),
            $this->scopedClasses = m::mock(ScopedClassesInterface::class),
        );
    }

    public function provideGetClasses(): \Generator
    {
        yield 'class' => [
            Listeners\CommandInterfaceListener::class,
            [
                Targets\ConsoleCommand::class,
                Targets\ConsoleCommandInterface::class,
                Targets\ConsoleCommandWithExtend::class,
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

    /**
     * @dataProvider provideGetClasses
     */
    public function testGetClasses(
        string $listener,
        array $expected,
    ): void {
        $classes = \array_map(
            fn (string $class) => new \ReflectionClass($class),
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
            ],
        );

        $definition = \iterator_to_array((new AttributesParser(new AttributeReader()))
            ->parse(new $listener))[0];

        if ($definition->scope === null) {
            $this->classes
                ->shouldReceive('getClasses')
                ->andReturn($classes);
        } else {
            $this->scopedClasses
                ->shouldReceive('getScopedClasses')
                ->with($definition->scope)
                ->andReturn($classes);
        }

        $classes = $this->locator->getClasses(
            $definition
        );

        $this->assertSame($expected, $classes);
    }
}
