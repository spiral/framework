<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use Nette\PhpGenerator\EnumType;
use PHPUnit\Framework\TestCase;
use Spiral\Reactor\Aggregator\EnumCases;
use Spiral\Reactor\EnumDeclaration;
use Spiral\Reactor\Partial;

final class EnumDeclarationTest extends TestCase
{
    public function testEnumDeclaration(): void
    {
        $enum = (new EnumDeclaration('MyEnum'))->setType('string');
        $enum->addCase('First', 'first');
        $enum->addCase('Second', 'second');
        $enum->addConstant('FOO', 'bar');
        $enum->addComment('Description of enum');
        $enum->addAttribute('SomeAttribute');
        $enum
            ->addMethod('getCase')
            ->setReturnType('string')
            ->addBody('return self::First->value;');

        self::assertSame(\preg_replace('/\s+/', '', '
            /**
             * Description of enum
             */
             #[SomeAttribute]
             enum MyEnum: string
             {
                public const FOO = \'bar\';

                case First = \'first\';
                case Second = \'second\';

                public function getCase(): string
                {
                    return self::First->value;
                }
             }
        '), \preg_replace('/\s+/', '', (string) $enum));
    }

    public function testName(): void
    {
        $enum = new EnumDeclaration('test');

        self::assertSame('test', $enum->getName());
    }

    public function testType(): void
    {
        $enum = new EnumDeclaration('test');

        self::assertNull($enum->getType());

        $enum->setType('string');
        self::assertSame('string', $enum->getType());
    }

    public function testCase(): void
    {
        $enum = new EnumDeclaration('Test');

        self::assertEmpty($enum->getCases());

        $enum->addCase('test');
        self::assertCount(1, $enum->getCases());
        self::assertInstanceOf(Partial\EnumCase::class, $enum->getCase('test'));

        $enum->removeCase('test');
        self::assertEmpty($enum->getCases());

        $enum->setCases(new EnumCases([new Partial\EnumCase('foo'), new Partial\EnumCase('bar')]));
        self::assertCount(2, $enum->getCases());
        self::assertInstanceOf(Partial\EnumCase::class, $enum->getCase('foo'));
        self::assertInstanceOf(Partial\EnumCase::class, $enum->getCase('bar'));
    }

    public function testImplements(): void
    {
        $enum = new EnumDeclaration('Test');

        self::assertEmpty($enum->getImplements());

        $enum->addImplement('Test');
        self::assertSame(['Test'], $enum->getImplements());

        $enum->setImplements(['Foo', 'Bar']);
        self::assertSame(['Foo', 'Bar'], $enum->getImplements());

        $enum->removeImplement('Bar');
        self::assertSame(['Foo'], $enum->getImplements());

        $enum->removeImplement('Foo');
        self::assertEmpty($enum->getImplements());
    }

    public function testAddMember(): void
    {
        $enum = new EnumDeclaration('Test');

        self::assertEmpty($enum->getCases());
        $enum->addMember(new Partial\EnumCase('test'));
        self::assertCount(1, $enum->getCases());
        self::assertInstanceOf(Partial\EnumCase::class, $enum->getCase('test'));

        self::assertEmpty($enum->getConstants());
        $enum->addMember(new Partial\Constant('TEST'));
        self::assertCount(1, $enum->getConstants());
        self::assertInstanceOf(Partial\Constant::class, $enum->getConstant('TEST'));

        self::assertEmpty($enum->getMethods());
        $enum->addMember(new Partial\Method('test'));
        self::assertCount(1, $enum->getMethods());
        self::assertInstanceOf(Partial\Method::class, $enum->getMethod('test'));

        self::assertEmpty($enum->getTraits());
        $enum->addMember(new Partial\TraitUse('test'));
        self::assertCount(1, $enum->getTraits());
        self::assertInstanceOf(Partial\TraitUse::class, $enum->getTrait('test'));
    }

    public function testIsEnum(): void
    {
        $enum = new EnumDeclaration('Test');

        self::assertTrue($enum->isEnum());

        self::assertFalse($enum->isInterface());
        self::assertFalse($enum->isClass());
        self::assertFalse($enum->isTrait());
    }

    public function testRender(): void
    {
        $expect = \preg_replace('/\s+/', '', '
            /**
             * Description of enum.
             * Second line
             */
             enum MyEnum: string implements Countable
             {
                 case First = \'first\';
                 case Second = \'second\';
             }
        ');

        $enum = new EnumDeclaration('MyEnum');
        $enum->addImplement(\Countable::class)->addComment("Description of enum.\nSecond line\n");
        $enum->addCase('First', 'first');
        $enum->addCase('Second', 'second');
        ;

        self::assertSame($expect, \preg_replace('/\s+/', '', $enum->render()));
        self::assertSame($expect, \preg_replace('/\s+/', '', $enum->__toString()));
    }

    public function testFromElement(): void
    {
        $enum = EnumDeclaration::fromElement(new EnumType('Test'));

        self::assertInstanceOf(EnumDeclaration::class, $enum);
        self::assertSame('Test', $enum->getName());
    }

    public function testGetElement(): void
    {
        $element = (new EnumDeclaration('Test'))->getElement();

        self::assertInstanceOf(EnumType::class, $element);
        self::assertSame('Test', $element->getName());
    }
}
