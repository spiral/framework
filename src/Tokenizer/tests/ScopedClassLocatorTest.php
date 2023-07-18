<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tests\Tokenizer\Classes\ClassA;
use Spiral\Tests\Tokenizer\Classes\ClassB;
use Spiral\Tests\Tokenizer\Classes\ClassC;
use Spiral\Tests\Tokenizer\Classes\Inner\ClassD;
use Spiral\Tokenizer\Config\TokenizerConfig;
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
        $this->container->bind(Tokenizer::class, $this->getTokenizer());
        $this->container->bindSingleton(ScopedClassesInterface::class, ScopedClassLocator::class);
    }

    public function testGetsClassesForExistsScope(): void
    {
        $classes = $this->container->get(ScopedClassesInterface::class)->getScopedClasses('foo');

        $this->assertArrayHasKey(ClassD::class, $classes);

        // Excluded
        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassB::class, $classes);
        $this->assertArrayNotHasKey(ClassC::class, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Excluded\ClassXX', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Bad_Class', $classes);
    }

    public function testGetsClassesForNotExistScope(): void
    {
        $classes = $this->container->get(ScopedClassesInterface::class)->getScopedClasses('bar');

        $this->assertArrayHasKey(self::class, $classes);
        $this->assertArrayHasKey(ClassA::class, $classes);
        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);
        $this->assertArrayHasKey(ClassD::class, $classes);

        // Excluded
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Excluded\ClassXX', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Bad_Class', $classes);
    }

    protected function getTokenizer(): Tokenizer
    {
        $config = new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude' => ['Excluded'],
            'scopes' => [
                'foo' => [
                    'directories' => [__DIR__.'/Classes/Inner'],
                    'exclude' => [],
                ],
            ],
        ]);

        return new Tokenizer($config);
    }
}
