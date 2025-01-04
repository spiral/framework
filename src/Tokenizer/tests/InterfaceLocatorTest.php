<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use Spiral\Tests\Tokenizer\Interfaces\BadInterface;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceA;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceB;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceC;
use Spiral\Tests\Tokenizer\Interfaces\Inner\InterfaceD;
use Spiral\Tests\Tokenizer\Fixtures\TestInterface;

final class InterfaceLocatorTest extends TestCase
{
    public function testInterfacesAll(): void
    {
        $tokenizer = $this->getTokenizer();

        //Direct loading
        $classes = $tokenizer->interfaceLocator()->getInterfaces();

        self::assertArrayHasKey(InterfaceA::class, $classes);
        self::assertArrayHasKey(InterfaceB::class, $classes);
        self::assertArrayHasKey(InterfaceC::class, $classes);
        self::assertArrayHasKey(InterfaceD::class, $classes);

        //Excluded
        self::assertArrayNotHasKey(InterfaceXX::class, $classes);
        self::assertArrayNotHasKey(BadInterface::class, $classes);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Interfaces\Bad_Interface', $classes);
    }


    public function testInterfacesByInterface(): void
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $classes = $tokenizer->interfaceLocator()->getInterfaces(TestInterface::class);

        self::assertArrayHasKey(InterfaceB::class, $classes);
        self::assertArrayHasKey(InterfaceC::class, $classes);

        self::assertArrayNotHasKey(InterfaceA::class, $classes);
        self::assertArrayNotHasKey(InterfaceD::class, $classes);
    }
}
