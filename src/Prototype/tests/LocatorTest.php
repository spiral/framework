<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype;

use PHPUnit\Framework\TestCase;
use Spiral\Prototype\PrototypeLocator;
use Spiral\Tests\Prototype\Fixtures\HydratedClass;
use Spiral\Tests\Prototype\Fixtures\TestClass;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedClassLocator;
use Spiral\Tokenizer\Tokenizer;

class LocatorTest extends TestCase
{
    public function testLocate(): void
    {
        $classes = $this->makeClasses();
        $l = new PrototypeLocator($classes);

        $this->assertArrayHasKey(TestClass::class, $l->getTargetClasses());
    }

    public function testLocateNot(): void
    {
        $classes = $this->makeClasses();
        $l = new PrototypeLocator($classes);

        $this->assertArrayNotHasKey(HydratedClass::class, $l->getTargetClasses());
    }

    private function makeClasses(): ScopedClassesInterface
    {
        return new ScopedClassLocator(new Tokenizer(new TokenizerConfig([
            'directories' => [],
            'scopes' => [
                'prototypes' => [
                    'directories' => [__DIR__ . '/Fixtures']
                ]
            ]
        ])));
    }
}
