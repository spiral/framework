<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use Spiral\Core\Container;
use Spiral\Tests\Tokenizer\Classes\ClassA;
use Spiral\Tests\Tokenizer\Classes\ClassB;
use Spiral\Tests\Tokenizer\Classes\ClassC;
use Spiral\Tests\Tokenizer\Classes\Inner\ClassD;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedClassLocator;
use Spiral\Tokenizer\Tokenizer;

final class ScopedClassLocatorTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->bind(Tokenizer::class, $this->getTokenizer(['scopes' => [
            'foo' => ['directories' => [__DIR__ . '/Classes/Inner'], 'exclude' => []],
        ]]));
        $this->container->bindSingleton(ScopedClassesInterface::class, ScopedClassLocator::class);
    }

    public function testGetsClassesForExistsScope(): void
    {
        $classes = $this->container->get(ScopedClassesInterface::class)->getScopedClasses('foo');

        self::assertArrayHasKey(ClassD::class, $classes);

        // Excluded
        self::assertArrayNotHasKey(self::class, $classes);
        self::assertArrayNotHasKey(ClassA::class, $classes);
        self::assertArrayNotHasKey(ClassB::class, $classes);
        self::assertArrayNotHasKey(ClassC::class, $classes);
        self::assertArrayNotHasKey(\Spiral\Tests\Tokenizer\Classes\Excluded\ClassXX::class, $classes);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Bad_Class', $classes);
    }

    public function testGetsClassesForNotExistScope(): void
    {
        $classes = $this->container->get(ScopedClassesInterface::class)->getScopedClasses('bar');

        self::assertArrayHasKey(self::class, $classes);
        self::assertArrayHasKey(ClassA::class, $classes);
        self::assertArrayHasKey(ClassB::class, $classes);
        self::assertArrayHasKey(ClassC::class, $classes);
        self::assertArrayHasKey(ClassD::class, $classes);

        // Excluded
        self::assertArrayNotHasKey(\Spiral\Tests\Tokenizer\Classes\Excluded\ClassXX::class, $classes);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Bad_Class', $classes);
    }
}
