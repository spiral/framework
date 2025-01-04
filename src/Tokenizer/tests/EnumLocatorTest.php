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

        self::assertArrayHasKey(EnumA::class, $enums);
        self::assertArrayHasKey(EnumB::class, $enums);
        self::assertArrayHasKey(EnumC::class, $enums);
        self::assertArrayHasKey(EnumD::class, $enums);

        //Excluded
        self::assertArrayNotHasKey(EnumXX::class, $enums);
        self::assertArrayNotHasKey(BadEnum::class, $enums);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Enums\Bad_Enum', $enums);
    }

    public function testEnumsByEnum(): void
    {
        $tokenizer = $this->getTokenizer();

        //By namespace
        $enums = $tokenizer->enumLocator()->getEnums(EnumD::class);

        self::assertArrayHasKey(EnumD::class, $enums);

        self::assertArrayNotHasKey(EnumA::class, $enums);
        self::assertArrayNotHasKey(EnumB::class, $enums);
        self::assertArrayNotHasKey(EnumC::class, $enums);
    }

    public function testEnumsByInterface(): void
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $enums = $tokenizer->enumLocator()->getEnums(TestInterface::class);

        self::assertArrayHasKey(EnumB::class, $enums);
        self::assertArrayHasKey(EnumC::class, $enums);

        self::assertArrayNotHasKey(EnumA::class, $enums);
        self::assertArrayNotHasKey(EnumD::class, $enums);
    }

    public function testEnumsByTrait(): void
    {
        $tokenizer = $this->getTokenizer();

        //By trait
        $enums = $tokenizer->enumLocator()->getEnums(TestTrait::class);

        self::assertArrayHasKey(EnumB::class, $enums);
        self::assertArrayHasKey(EnumC::class, $enums);

        self::assertArrayNotHasKey(EnumA::class, $enums);
        self::assertArrayNotHasKey(EnumD::class, $enums);
    }
}
