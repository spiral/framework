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

        $this->assertArrayHasKey(InterfaceA::class, $classes);
        $this->assertArrayHasKey(InterfaceB::class, $classes);
        $this->assertArrayHasKey(InterfaceC::class, $classes);
        $this->assertArrayHasKey(InterfaceD::class, $classes);

        //Excluded
        $this->assertArrayNotHasKey(InterfaceXX::class, $classes);
        $this->assertArrayNotHasKey(BadInterface::class, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Interfaces\Bad_Interface', $classes);
    }


    public function testInterfacesByInterface(): void
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $classes = $tokenizer->interfaceLocator()->getInterfaces(TestInterface::class);

        $this->assertArrayHasKey(InterfaceB::class, $classes);
        $this->assertArrayHasKey(InterfaceC::class, $classes);

        $this->assertArrayNotHasKey(InterfaceA::class, $classes);
        $this->assertArrayNotHasKey(InterfaceD::class, $classes);
    }
}
