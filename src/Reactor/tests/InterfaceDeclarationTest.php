<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use Nette\PhpGenerator\InterfaceType;
use PHPUnit\Framework\TestCase;
use Spiral\Reactor\InterfaceDeclaration;
use Spiral\Reactor\Partial\Constant;
use Spiral\Reactor\Partial\Method;

final class InterfaceDeclarationTest extends TestCase
{
    public function testIsInterface(): void
    {
        $interface = new InterfaceDeclaration('TestInterface');

        self::assertTrue($interface->isInterface());

        self::assertFalse($interface->isClass());
        self::assertFalse($interface->isEnum());
        self::assertFalse($interface->isTrait());
    }

    public function testExtends(): void
    {
        $interface = new InterfaceDeclaration('TestInterface');

        self::assertEmpty($interface->getExtends());

        $interface->addExtend(\Countable::class);
        self::assertCount(1, $interface->getExtends());
        self::assertSame([\Countable::class], $interface->getExtends());

        $interface->addExtend(\ArrayAccess::class);
        self::assertCount(2, $interface->getExtends());
        self::assertSame([\Countable::class, \ArrayAccess::class], $interface->getExtends());

        $interface->setExtends(\IteratorAggregate::class);
        self::assertCount(1, $interface->getExtends());
        self::assertSame([\IteratorAggregate::class], $interface->getExtends());
    }

    public function testAddMember(): void
    {
        $interface = new InterfaceDeclaration('TestInterface');

        self::assertEmpty($interface->getConstants());
        $interface->addMember(new Constant('TEST'));
        self::assertCount(1, $interface->getConstants());
        self::assertInstanceOf(Constant::class, $interface->getConstant('TEST'));

        self::assertEmpty($interface->getMethods());
        $interface->addMember(new Method('test'));
        self::assertCount(1, $interface->getMethods());
        self::assertInstanceOf(Method::class, $interface->getMethod('test'));
    }

    public function testFromElement(): void
    {
        $interface = InterfaceDeclaration::fromElement(new InterfaceType('TestInterface'));

        self::assertInstanceOf(InterfaceDeclaration::class, $interface);
        self::assertSame('TestInterface', $interface->getName());
    }

    public function testGetElement(): void
    {
        $element = (new InterfaceDeclaration('TestInterface'))->getElement();

        self::assertInstanceOf(InterfaceType::class, $element);
        self::assertSame('TestInterface', $element->getName());
    }
}
