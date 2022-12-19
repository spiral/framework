<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Partial;

use PHPUnit\Framework\TestCase;
use Nette\PhpGenerator\Method as NetteMethod;
use Spiral\Reactor\Aggregator\Parameters;
use Spiral\Reactor\Partial\Attribute;
use Spiral\Reactor\Partial\Method;
use Spiral\Reactor\Partial\Parameter;
use Spiral\Reactor\Partial\PromotedParameter;
use Spiral\Reactor\Partial\Visibility;

final class MethodTest extends TestCase
{
    public function testAttribute(): void
    {
        $method = new Method('test');
        $this->assertEmpty($method->getAttributes());

        $method->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        $this->assertCount(1, $method->getAttributes());

        $method->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar'])
        ]);
        $this->assertCount(2, $method->getAttributes());
    }

    public function testComment(): void
    {
        $method = new Method('test');
        $this->assertNull($method->getComment());

        $method->setComment('/** Awesome method */');
        $this->assertSame('/** Awesome method */', $method->getComment());

        $method->setComment(null);
        $this->assertNull($method->getComment());

        $method->setComment(['/** Line one */', '/** Line two */']);
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $method->getComment())
        );

        $method->setComment(null);
        $method->addComment('/** Line one */');
        $method->addComment('/** Line two */');
        $this->assertSame(
            \preg_replace('/\s+/', '', '/** Line one *//** Line two */'),
            \preg_replace('/\s+/', '', $method->getComment())
        );
    }

    public function testGetName(): void
    {
        $method = new Method('test');

        $this->assertSame('test', $method->getName());
    }

    public function testBody(): void
    {
        $method = new Method('test');

        $this->assertEmpty($method->getBody());

        $method->setBody('return 1;');
        $this->assertSame('return 1;', $method->getBody());

        $method = new Method('test');
        $method->addBody('$var = 1;');
        $method->addBody('return $var;');
        $this->assertSame('$var=1;return$var;', \preg_replace('/\s+/', '', $method->getBody()));
    }

    public function testParameter(): void
    {
        $method = new Method('test');
        $this->assertEmpty($method->getParameters());

        $method->addParameter('test');
        $this->assertCount(1, $method->getParameters());

        $method->setParameters(new Parameters([
            new Parameter('name'),
            new Parameter('name2')
        ]));
        $this->assertCount(2, $method->getParameters());
        $this->assertTrue($method->getParameters()->has('name'));
        $this->assertTrue($method->getParameters()->has('name2'));

        $method->removeParameter('name');
        $this->assertCount(1, $method->getParameters());
        $this->assertFalse($method->getParameters()->has('name'));
    }

    public function testVariadic(): void
    {
        $method = new Method('test');

        $this->assertFalse($method->isVariadic());

        $method->setVariadic(true);
        $this->assertTrue($method->isVariadic());
    }

    public function testReturnType(): void
    {
        $method = new Method('test');

        $this->assertNull($method->getReturnType());

        $method->setReturnType('string');
        $this->assertSame('string', $method->getReturnType());

        $this->assertFalse($method->isReturnNullable());

        $method->setReturnNullable(true);
        $this->assertTrue($method->isReturnNullable());
    }

    public function testReturnReference(): void
    {
        $method = new Method('test');

        $this->assertFalse($method->getReturnReference());

        $method->setReturnReference(true);
        $this->assertTrue($method->getReturnReference());
    }

    public function testVisibility(): void
    {
        $method = new Method('test');
        $this->assertNull($method->getVisibility());

        $method->setVisibility(Visibility::PUBLIC);
        $this->assertSame(Visibility::PUBLIC, $method->getVisibility());
        $this->assertTrue($method->isPublic());
        $this->assertFalse($method->isProtected());
        $this->assertFalse($method->isPrivate());

        $method->setVisibility(Visibility::PROTECTED);
        $this->assertSame(Visibility::PROTECTED, $method->getVisibility());
        $this->assertFalse($method->isPublic());
        $this->assertTrue($method->isProtected());
        $this->assertFalse($method->isPrivate());

        $method->setVisibility(Visibility::PRIVATE);
        $this->assertSame(Visibility::PRIVATE, $method->getVisibility());
        $this->assertFalse($method->isPublic());
        $this->assertFalse($method->isProtected());
        $this->assertTrue($method->isPrivate());

        $method->setPublic();
        $this->assertSame(Visibility::PUBLIC, $method->getVisibility());
        $this->assertTrue($method->isPublic());
        $this->assertFalse($method->isProtected());
        $this->assertFalse($method->isPrivate());

        $method->setProtected();
        $this->assertSame(Visibility::PROTECTED, $method->getVisibility());
        $this->assertFalse($method->isPublic());
        $this->assertTrue($method->isProtected());
        $this->assertFalse($method->isPrivate());

        $method->setPrivate();
        $this->assertSame(Visibility::PRIVATE, $method->getVisibility());
        $this->assertFalse($method->isPublic());
        $this->assertFalse($method->isProtected());
        $this->assertTrue($method->isPrivate());
    }

    public function testStatic(): void
    {
        $method = new Method('test');;

        $this->assertFalse($method->isStatic());

        $method->setStatic();
        $this->assertTrue($method->isStatic());

        $method->setStatic(false);
        $this->assertFalse($method->isStatic());

        $method->setStatic(true);
        $this->assertTrue($method->isStatic());
    }

    public function testFinal(): void
    {
        $method = new Method('test');;

        $this->assertFalse($method->isFinal());

        $method->setFinal();
        $this->assertTrue($method->isFinal());

        $method->setFinal(false);
        $this->assertFalse($method->isFinal());

        $method->setFinal(true);
        $this->assertTrue($method->isFinal());
    }

    public function testAbstract(): void
    {
        $method = new Method('test');;

        $this->assertFalse($method->isAbstract());

        $method->setAbstract();
        $this->assertTrue($method->isAbstract());

        $method->setAbstract(false);
        $this->assertFalse($method->isAbstract());

        $method->setAbstract(true);
        $this->assertTrue($method->isAbstract());
    }

    public function testAddPromotedParameter(): void
    {
        $method = new Method('test');
        $param = $method->addPromotedParameter('test');

        $this->assertInstanceOf(PromotedParameter::class, $param);
        $this->assertSame('test', $param->getName());
    }

    public function testAddPromotedParameterWithoutDefaultValue(): void
    {
        $method = new Method('test');
        $param = $method->addPromotedParameter('test');

        $this->assertFalse($param->hasDefaultValue());
    }

    public function testAddPromotedParameterWithDefaultNullValue(): void
    {
        $method = new Method('test');
        $param = $method->addPromotedParameter('test', null);
        $param->setNullable();

        $this->assertTrue($param->hasDefaultValue());
        $this->assertNull($param->getDefaultValue());
    }

    public function testAddPromotedParameterWithDefaultValue(): void
    {
        $method = new Method('test');
        $param = $method->addPromotedParameter('test', 'foo');

        $this->assertTrue($param->hasDefaultValue());
        $this->assertSame('foo', $param->getDefaultValue());
    }

    public function testRender(): void
    {
        $expect = preg_replace('/\s+/', '', '
            public function test(): int
            {
                return 1;
            }');

        $method = new Method('test');
        $method->setReturnType('int')->setPublic()->setBody('return 1;');

        $this->assertSame($expect, preg_replace('/\s+/', '', $method->__toString()));
    }

    public function testFromElement(): void
    {
        $method = Method::fromElement(new NetteMethod('test'));

        $this->assertInstanceOf(Method::class, $method);
        $this->assertSame('test', $method->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Method('test'))->getElement();

        $this->assertInstanceOf(NetteMethod::class, $element);
        $this->assertSame('test', $element->getName());
    }
}
