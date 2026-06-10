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
        self::assertEmpty($method->getAttributes());

        $method->addAttribute('test', ['name' => 'foo', 'otherName' => 'bar']);
        self::assertCount(1, $method->getAttributes());

        $method->setAttributes([
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
            new Attribute('name', ['name' => 'foo', 'otherName' => 'bar']),
        ]);
        self::assertCount(2, $method->getAttributes());
    }

    public function testComment(): void
    {
        $method = new Method('test');
        self::assertNull($method->getComment());

        $method->setComment('/** Awesome method */');
        self::assertSame('/** Awesome method */', $method->getComment());

        $method->setComment(null);
        self::assertNull($method->getComment());

        $method->setComment(['/** Line one */', '/** Line two */']);
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $method->getComment()));

        $method->setComment(null);
        $method->addComment('/** Line one */');
        $method->addComment('/** Line two */');
        self::assertSame(\preg_replace('/\s+/', '', '/** Line one *//** Line two */'), \preg_replace('/\s+/', '', $method->getComment()));
    }

    public function testGetName(): void
    {
        $method = new Method('test');

        self::assertSame('test', $method->getName());
    }

    public function testBody(): void
    {
        $method = new Method('test');

        self::assertEmpty($method->getBody());

        $method->setBody('return 1;');
        self::assertSame('return 1;', $method->getBody());

        $method = new Method('test');
        $method->addBody('$var = 1;');
        $method->addBody('return $var;');
        self::assertSame('$var=1;return$var;', \preg_replace('/\s+/', '', $method->getBody()));
    }

    public function testParameter(): void
    {
        $method = new Method('test');
        self::assertEmpty($method->getParameters());

        $method->addParameter('test');
        self::assertCount(1, $method->getParameters());

        $method->setParameters(new Parameters([
            new Parameter('name'),
            new Parameter('name2'),
        ]));
        self::assertCount(2, $method->getParameters());
        self::assertTrue($method->getParameters()->has('name'));
        self::assertTrue($method->getParameters()->has('name2'));

        $method->removeParameter('name');
        self::assertCount(1, $method->getParameters());
        self::assertFalse($method->getParameters()->has('name'));
    }

    public function testVariadic(): void
    {
        $method = new Method('test');

        self::assertFalse($method->isVariadic());

        $method->setVariadic(true);
        self::assertTrue($method->isVariadic());
    }

    public function testReturnType(): void
    {
        $method = new Method('test');

        self::assertNull($method->getReturnType());

        $method->setReturnType('string');
        self::assertSame('string', $method->getReturnType());

        self::assertFalse($method->isReturnNullable());

        $method->setReturnNullable(true);
        self::assertTrue($method->isReturnNullable());
    }

    public function testReturnReference(): void
    {
        $method = new Method('test');

        self::assertFalse($method->getReturnReference());

        $method->setReturnReference(true);
        self::assertTrue($method->getReturnReference());
    }

    public function testVisibility(): void
    {
        $method = new Method('test');
        self::assertNull($method->getVisibility());

        $method->setVisibility(Visibility::PUBLIC);
        self::assertSame(Visibility::PUBLIC, $method->getVisibility());
        self::assertTrue($method->isPublic());
        self::assertFalse($method->isProtected());
        self::assertFalse($method->isPrivate());

        $method->setVisibility(Visibility::PROTECTED);
        self::assertSame(Visibility::PROTECTED, $method->getVisibility());
        self::assertFalse($method->isPublic());
        self::assertTrue($method->isProtected());
        self::assertFalse($method->isPrivate());

        $method->setVisibility(Visibility::PRIVATE);
        self::assertSame(Visibility::PRIVATE, $method->getVisibility());
        self::assertFalse($method->isPublic());
        self::assertFalse($method->isProtected());
        self::assertTrue($method->isPrivate());

        $method->setPublic();
        self::assertSame(Visibility::PUBLIC, $method->getVisibility());
        self::assertTrue($method->isPublic());
        self::assertFalse($method->isProtected());
        self::assertFalse($method->isPrivate());

        $method->setProtected();
        self::assertSame(Visibility::PROTECTED, $method->getVisibility());
        self::assertFalse($method->isPublic());
        self::assertTrue($method->isProtected());
        self::assertFalse($method->isPrivate());

        $method->setPrivate();
        self::assertSame(Visibility::PRIVATE, $method->getVisibility());
        self::assertFalse($method->isPublic());
        self::assertFalse($method->isProtected());
        self::assertTrue($method->isPrivate());
    }

    public function testStatic(): void
    {
        $method = new Method('test');
        ;

        self::assertFalse($method->isStatic());

        $method->setStatic();
        self::assertTrue($method->isStatic());

        $method->setStatic(false);
        self::assertFalse($method->isStatic());

        $method->setStatic(true);
        self::assertTrue($method->isStatic());
    }

    public function testFinal(): void
    {
        $method = new Method('test');
        ;

        self::assertFalse($method->isFinal());

        $method->setFinal();
        self::assertTrue($method->isFinal());

        $method->setFinal(false);
        self::assertFalse($method->isFinal());

        $method->setFinal(true);
        self::assertTrue($method->isFinal());
    }

    public function testAbstract(): void
    {
        $method = new Method('test');
        ;

        self::assertFalse($method->isAbstract());

        $method->setAbstract();
        self::assertTrue($method->isAbstract());

        $method->setAbstract(false);
        self::assertFalse($method->isAbstract());

        $method->setAbstract(true);
        self::assertTrue($method->isAbstract());
    }

    public function testAddPromotedParameter(): void
    {
        $method = new Method('test');
        $param = $method->addPromotedParameter('test');

        self::assertInstanceOf(PromotedParameter::class, $param);
        self::assertSame('test', $param->getName());
    }

    public function testAddPromotedParameterWithoutDefaultValue(): void
    {
        $method = new Method('test');
        $param = $method->addPromotedParameter('test');

        self::assertFalse($param->hasDefaultValue());
    }

    public function testAddPromotedParameterWithDefaultNullValue(): void
    {
        $method = new Method('test');
        $param = $method->addPromotedParameter('test', null);
        $param->setNullable();

        self::assertTrue($param->hasDefaultValue());
        self::assertNull($param->getDefaultValue());
    }

    public function testAddPromotedParameterWithDefaultValue(): void
    {
        $method = new Method('test');
        $param = $method->addPromotedParameter('test', 'foo');

        self::assertTrue($param->hasDefaultValue());
        self::assertSame('foo', $param->getDefaultValue());
    }

    public function testRender(): void
    {
        $expect = \preg_replace('/\s+/', '', '
            public function test(): int
            {
                return 1;
            }');

        $method = new Method('test');
        $method->setReturnType('int')->setPublic()->setBody('return 1;');

        self::assertSame($expect, \preg_replace('/\s+/', '', $method->__toString()));
    }

    public function testFromElement(): void
    {
        $method = Method::fromElement(new NetteMethod('test'));

        self::assertInstanceOf(Method::class, $method);
        self::assertSame('test', $method->getName());
    }

    public function testGetElement(): void
    {
        $element = (new Method('test'))->getElement();

        self::assertInstanceOf(NetteMethod::class, $element);
        self::assertSame('test', $element->getName());
    }
}
