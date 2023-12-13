<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use Spiral\Tests\Tokenizer\Enums\BadEnum;
use Spiral\Tests\Tokenizer\Enums\EnumA;
use Spiral\Tests\Tokenizer\Enums\EnumB;
use Spiral\Tests\Tokenizer\Enums\EnumC;
use Spiral\Tests\Tokenizer\Enums\Excluded\EnumXX;
use Spiral\Tests\Tokenizer\Enums\Inner\EnumD;
use Spiral\Tests\Tokenizer\Fixtures\TestInterface;
use Spiral\Tests\Tokenizer\Fixtures\TestTrait;

final class EnumLocatorTest extends TestCase
{
    public function testEnumsAll(): void
    {
        $tokenizer = $this->getTokenizer();

        //Direct loading
        $enums = $tokenizer->enumLocator()->getEnums();

        $this->assertArrayHasKey(EnumA::class, $enums);
        $this->assertArrayHasKey(EnumB::class, $enums);
        $this->assertArrayHasKey(EnumC::class, $enums);
        $this->assertArrayHasKey(EnumD::class, $enums);

        //Excluded
        $this->assertArrayNotHasKey(EnumXX::class, $enums);
        $this->assertArrayNotHasKey(BadEnum::class, $enums);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Enums\Bad_Enum', $enums);
    }

    public function testEnumsByEnum(): void
    {
        $tokenizer = $this->getTokenizer();

        //By namespace
        $enums = $tokenizer->enumLocator()->getEnums(EnumD::class);

        $this->assertArrayHasKey(EnumD::class, $enums);

        $this->assertArrayNotHasKey(EnumA::class, $enums);
        $this->assertArrayNotHasKey(EnumB::class, $enums);
        $this->assertArrayNotHasKey(EnumC::class, $enums);
    }

    public function testEnumsByInterface(): void
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $enums = $tokenizer->enumLocator()->getEnums(TestInterface::class);

        $this->assertArrayHasKey(EnumB::class, $enums);
        $this->assertArrayHasKey(EnumC::class, $enums);

        $this->assertArrayNotHasKey(EnumA::class, $enums);
        $this->assertArrayNotHasKey(EnumD::class, $enums);
    }

    public function testEnumsByTrait(): void
    {
        $tokenizer = $this->getTokenizer();

        //By trait
        $enums = $tokenizer->enumLocator()->getEnums(TestTrait::class);

        $this->assertArrayHasKey(EnumB::class, $enums);
        $this->assertArrayHasKey(EnumC::class, $enums);

        $this->assertArrayNotHasKey(EnumA::class, $enums);
        $this->assertArrayNotHasKey(EnumD::class, $enums);
    }
}
