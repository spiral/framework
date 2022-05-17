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

        $this->assertTrue($interface->isInterface());

        $this->assertFalse($interface->isClass());
        $this->assertFalse($interface->isEnum());
        $this->assertFalse($interface->isTrait());
    }

    public function testExtends(): void
    {
        $interface = new InterfaceDeclaration('TestInterface');

        $this->assertEmpty($interface->getExtends());

        $interface->addExtend(\Countable::class);
        $this->assertCount(1, $interface->getExtends());
        $this->assertSame([\Countable::class], $interface->getExtends());

        $interface->addExtend(\ArrayAccess::class);
        $this->assertCount(2, $interface->getExtends());
        $this->assertSame([\Countable::class, \ArrayAccess::class], $interface->getExtends());

        $interface->setExtends(\IteratorAggregate::class);
        $this->assertCount(1, $interface->getExtends());
        $this->assertSame([\IteratorAggregate::class], $interface->getExtends());
    }

    public function testAddMember(): void
    {
        $interface = new InterfaceDeclaration('TestInterface');

        $this->assertEmpty($interface->getConstants());
        $interface->addMember(new Constant('TEST'));
        $this->assertCount(1, $interface->getConstants());
        $this->assertInstanceOf(Constant::class, $interface->getConstant('TEST'));

        $this->assertEmpty($interface->getMethods());
        $interface->addMember(new Method('test'));
        $this->assertCount(1, $interface->getMethods());
        $this->assertInstanceOf(Method::class, $interface->getMethod('test'));
    }

    public function testFromElement(): void
    {
        $interface = InterfaceDeclaration::fromElement(new InterfaceType('TestInterface'));

        $this->assertInstanceOf(InterfaceDeclaration::class, $interface);
        $this->assertSame('TestInterface', $interface->getName());
    }

    public function testGetElement(): void
    {
        $element = (new InterfaceDeclaration('TestInterface'))->getElement();

        $this->assertInstanceOf(InterfaceType::class, $element);
        $this->assertSame('TestInterface', $element->getName());
    }
}
