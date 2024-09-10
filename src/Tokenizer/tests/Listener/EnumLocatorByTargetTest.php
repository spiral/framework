<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Spiral\Tests\Tokenizer\Classes\Listeners\CommandInterfaceListener;
use Spiral\Tests\Tokenizer\Enums\Targets\EnumWithAllTargets;
use Spiral\Tests\Tokenizer\Classes\Listeners\TraitListener;
use Spiral\Tests\Tokenizer\Enums\Targets\EnumWithTrait;
use Spiral\Tests\Tokenizer\Classes\Listeners\ControllerListener;
use Spiral\Tests\Tokenizer\Enums\Targets\EnumWithAttributeOnClass;
use Spiral\Tests\Tokenizer\Enums\Targets\EnumWithoutTargets;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Tests\Tokenizer\Classes\Listeners;
use Spiral\Tests\Tokenizer\Enums\Targets;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\EnumsInterface;
use Spiral\Tokenizer\Listener\ClassLocatorByTarget;
use Spiral\Tokenizer\Listener\EnumLocatorByTarget;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedEnumsInterface;

final class EnumLocatorByTargetTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private EnumLocatorByTarget $locator;
    private EnumsInterface|m\LegacyMockInterface|m\MockInterface $classes;
    private ScopedEnumsInterface|m\LegacyMockInterface|m\MockInterface $scopedClasses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = new EnumLocatorByTarget(
            $this->classes = m::mock(EnumsInterface::class),
            $this->scopedClasses = m::mock(ScopedEnumsInterface::class),
        );
    }

    public static function provideGetEnums(): \Generator
    {
        yield 'class' => [
            CommandInterfaceListener::class,
            [
                EnumWithAllTargets::class,
            ],
        ];

        yield 'trait' => [
            TraitListener::class,
            [
                EnumWithTrait::class,
            ],
        ];

        yield 'attribute-on-class' => [
            ControllerListener::class,
            [
                EnumWithAttributeOnClass::class,
                EnumWithAllTargets::class,
            ],
        ];
    }

    #[DataProvider('provideGetEnums')]
    public function testGetEnums(
        string $listener,
        array $expected,
    ): void {
        $enums = \array_map(
            fn (string $class) => new \ReflectionEnum($class),
            [
                EnumWithAttributeOnClass::class,
                EnumWithAllTargets::class,
                EnumWithTrait::class,
                EnumWithoutTargets::class,
            ],
        );

        $attr = new \ReflectionClass($listener);
        $attr = $attr->getAttributes()[0];
        $attribute = $attr->newInstance();

        if ($attribute->getScope() === null) {
            $this->classes
                ->shouldReceive('getEnums')
                ->andReturn($enums);
        } else {
            $this->scopedClasses
                ->shouldReceive('getScopedEnums')
                ->with($attribute->getScope())
                ->andReturn($enums);
        }

        $enums = $this->locator->getEnums(
            $attribute
        );

        $this->assertSame($expected, $enums);
    }
}
