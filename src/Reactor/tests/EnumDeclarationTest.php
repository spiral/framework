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

        $this->assertSame(preg_replace('/\s+/', '', '
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
        '),
            preg_replace('/\s+/', '', (string) $enum)
        );
    }

    public function testName(): void
    {
        $enum = new EnumDeclaration('test');

        $this->assertSame('test', $enum->getName());
    }

    public function testType(): void
    {
        $enum = new EnumDeclaration('test');

        $this->assertNull($enum->getType());

        $enum->setType('string');
        $this->assertSame('string', $enum->getType());
    }

    public function testCase(): void
    {
        $enum = new EnumDeclaration('Test');

        $this->assertEmpty($enum->getCases());

        $enum->addCase('test');
        $this->assertCount(1, $enum->getCases());
        $this->assertInstanceOf(Partial\EnumCase::class, $enum->getCase('test'));

        $enum->removeCase('test');
        $this->assertEmpty($enum->getCases());

        $enum->setCases(new EnumCases([new Partial\EnumCase('foo'), new Partial\EnumCase('bar')]));
        $this->assertCount(2, $enum->getCases());
        $this->assertInstanceOf(Partial\EnumCase::class, $enum->getCase('foo'));
        $this->assertInstanceOf(Partial\EnumCase::class, $enum->getCase('bar'));
    }

    public function testImplements(): void
    {
        $enum = new EnumDeclaration('Test');

        $this->assertEmpty($enum->getImplements());

        $enum->addImplement('Test');
        $this->assertSame(['Test'], $enum->getImplements());

        $enum->setImplements(['Foo', 'Bar']);
        $this->assertSame(['Foo', 'Bar'], $enum->getImplements());

        $enum->removeImplement('Bar');
        $this->assertSame(['Foo'], $enum->getImplements());

        $enum->removeImplement('Foo');
        $this->assertEmpty($enum->getImplements());
    }

    public function testAddMember(): void
    {
        $enum = new EnumDeclaration('Test');

        $this->assertEmpty($enum->getCases());
        $enum->addMember(new Partial\EnumCase('test'));
        $this->assertCount(1, $enum->getCases());
        $this->assertInstanceOf(Partial\EnumCase::class, $enum->getCase('test'));

        $this->assertEmpty($enum->getConstants());
        $enum->addMember(new Partial\Constant('TEST'));
        $this->assertCount(1, $enum->getConstants());
        $this->assertInstanceOf(Partial\Constant::class, $enum->getConstant('TEST'));

        $this->assertEmpty($enum->getMethods());
        $enum->addMember(new Partial\Method('test'));
        $this->assertCount(1, $enum->getMethods());
        $this->assertInstanceOf(Partial\Method::class, $enum->getMethod('test'));

        $this->assertEmpty($enum->getTraits());
        $enum->addMember(new Partial\TraitUse('test'));
        $this->assertCount(1, $enum->getTraits());
        $this->assertInstanceOf(Partial\TraitUse::class, $enum->getTrait('test'));
    }

    public function testIsEnum(): void
    {
        $enum = new EnumDeclaration('Test');

        $this->assertTrue($enum->isEnum());

        $this->assertFalse($enum->isInterface());
        $this->assertFalse($enum->isClass());
        $this->assertFalse($enum->isTrait());
    }

    public function testRender(): void
    {
        $expect = preg_replace('/\s+/', '', '
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
        $enum->addCase('Second', 'second');;

        $this->assertSame($expect, preg_replace('/\s+/', '', $enum->render()));
        $this->assertSame($expect, preg_replace('/\s+/', '', $enum->__toString()));
    }

    public function testFromElement(): void
    {
        $enum = EnumDeclaration::fromElement(new EnumType('Test'));

        $this->assertInstanceOf(EnumDeclaration::class, $enum);
        $this->assertSame('Test', $enum->getName());
    }

    public function testGetElement(): void
    {
        $element = (new EnumDeclaration('Test'))->getElement();

        $this->assertInstanceOf(EnumType::class, $element);
        $this->assertSame('Test', $element->getName());
    }
}
