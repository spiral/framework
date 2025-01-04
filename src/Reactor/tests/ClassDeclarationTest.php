<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use Nette\PhpGenerator\ClassType;
use PHPUnit\Framework\TestCase;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\Partial;

final class ClassDeclarationTest extends TestCase
{
    public function testSimpleClassDeclaration(): void
    {
        $declaration = new ClassDeclaration('MyClass');
        $declaration->setFinal()
            ->setExtends(self::class)
            ->addImplement(\Countable::class)
            ->addComment("Description of class.\nSecond line\n")
            ->addComment('@property-read Nette\Forms\Form $form');

        self::assertSame(preg_replace('/\s+/', '', '/**
            * Description of class.
            * Second line
            *
            * @property-read Nette\Forms\Form $form
            */
            final class MyClass extends Spiral\Tests\Reactor\ClassDeclarationTest implements Countable
            {
            }'), preg_replace('/\s+/', '', (string) $declaration));
    }

    public function testClassDeclarationWithConstants(): void
    {
        $declaration = new ClassDeclaration('MyClass');
        $declaration
            ->setExtends(self::class)
            ->addImplement(\Countable::class);

        $declaration
            ->addConstant('PRIVATE', 123)
            ->setVisibility(Partial\Visibility::PRIVATE);

        $declaration
            ->addConstant('PROTECTED', 456)
            ->setVisibility(Partial\Visibility::PROTECTED);

        $declaration
            ->addConstant('PUBLIC', 789)
            ->setVisibility(Partial\Visibility::PUBLIC);

        $declaration
            ->addConstant('WITH_COMMENT', 'foo')
            ->setComment('Some comment');

        $declaration
            ->addConstant('FINAL', 'final')
            ->setFinal();

        $declaration
            ->addConstant('WITH_ATTRIBUTE', 'attr')
            ->addAttribute('Foo\Cached', ['mode' => true]);

        self::assertSame(preg_replace('/\s+/', '', '
        class MyClass extends Spiral\Tests\Reactor\ClassDeclarationTest implements Countable
            {
                private const PRIVATE = 123;
                protected const PROTECTED = 456;
                public const PUBLIC = 789;

                /** Some comment */
                public const WITH_COMMENT = \'foo\';
                final public const FINAL = \'final\';

                #[Foo\Cached(mode: true)]
                public const WITH_ATTRIBUTE = \'attr\';
            }'), preg_replace('/\s+/', '', (string) $declaration));
    }

    public function testClassDeclarationWithMethods(): void
    {
        $declaration = new ClassDeclaration('MyClass');
        $declaration
            ->setExtends(self::class)
            ->addImplement(\Countable::class);

        $method = $declaration->addMethod('count')
            ->addComment('Count it.')
            ->setFinal()
            ->setProtected()
            ->setReturnType('?int')
            ->setBody('return count($items ?: $this->items);');

        $method->addParameter('items', [])
            ->setReference()
            ->setType('array');

        self::assertSame(preg_replace('/\s+/', '', '
        class MyClass extends Spiral\Tests\Reactor\ClassDeclarationTest implements Countable
        {
           /**
            * Count it.
            */
            final protected function count(array &$items = []): ?int
            {
                return count($items ?: $this->items);
            }
        }'), preg_replace('/\s+/', '', (string) $declaration));
    }

    public function testName(): void
    {
        $class = new ClassDeclaration();

        self::assertNull($class->getName());

        $class->setName('test');
        self::assertSame('Test', $class->getName()); // classified

        $class->setName('Test');
        self::assertSame('Test', $class->getName());

        $class->setName(null);
        self::assertNull($class->getName());
    }

    public function testFinal(): void
    {
        $class = new ClassDeclaration();

        self::assertFalse($class->isFinal());

        $class->setFinal();
        self::assertTrue($class->isFinal());

        $class->setFinal(false);
        self::assertFalse($class->isFinal());

        $class->setFinal(true);
        self::assertTrue($class->isFinal());
    }

    public function testAbstract(): void
    {
        $class = new ClassDeclaration();

        self::assertFalse($class->isAbstract());

        $class->setAbstract();
        self::assertTrue($class->isAbstract());

        $class->setAbstract(false);
        self::assertFalse($class->isAbstract());

        $class->setAbstract(true);
        self::assertTrue($class->isAbstract());
    }

    public function testExtends(): void
    {
        $class = new ClassDeclaration();

        self::assertNull($class->getExtends());

        $class->setExtends('Test');
        self::assertSame('Test', $class->getExtends());
    }

    public function testImplements(): void
    {
        $class = new ClassDeclaration();

        self::assertEmpty($class->getImplements());

        $class->addImplement('Test');
        self::assertSame(['Test'], $class->getImplements());

        $class->setImplements(['Foo', 'Bar']);
        self::assertSame(['Foo', 'Bar'], $class->getImplements());

        $class->removeImplement('Bar');
        self::assertSame(['Foo'], $class->getImplements());

        $class->removeImplement('Foo');
        self::assertEmpty($class->getImplements());
    }

    public function testAddMember(): void
    {
        $class = new ClassDeclaration();

        self::assertEmpty($class->getConstants());
        $class->addMember(new Partial\Constant('TEST'));
        self::assertCount(1, $class->getConstants());
        self::assertInstanceOf(Partial\Constant::class, $class->getConstant('TEST'));

        self::assertEmpty($class->getMethods());
        $class->addMember(new Partial\Method('test'));
        self::assertCount(1, $class->getMethods());
        self::assertInstanceOf(Partial\Method::class, $class->getMethod('test'));

        self::assertEmpty($class->getProperties());
        $class->addMember(new Partial\Property('test'));
        self::assertCount(1, $class->getProperties());
        self::assertInstanceOf(Partial\Property::class, $class->getProperty('test'));

        self::assertEmpty($class->getTraits());
        $class->addMember(new Partial\TraitUse('test'));
        self::assertCount(1, $class->getTraits());
        self::assertInstanceOf(Partial\TraitUse::class, $class->getTrait('test'));
    }

    public function testIsClass(): void
    {
        $class = new ClassDeclaration();

        self::assertTrue($class->isClass());

        self::assertFalse($class->isInterface());
        self::assertFalse($class->isEnum());
        self::assertFalse($class->isTrait());
    }

    public function testRender(): void
    {
        $expect = preg_replace('/\s+/', '', '/**
            * Description of class.
            * Second line
            *
            * @property-read $form
            */
            final class MyClass extends Spiral\Tests\Reactor\ClassDeclarationTest implements Countable
            {
            }');

        $class = new ClassDeclaration('MyClass');
        $class->setFinal()
            ->setExtends(self::class)
            ->addImplement(\Countable::class)
            ->addComment("Description of class.\nSecond line\n")
            ->addComment('@property-read $form');

        self::assertSame($expect, preg_replace('/\s+/', '', $class->render()));
        self::assertSame($expect, preg_replace('/\s+/', '', $class->__toString()));
    }

    public function testRenderPromotedParameter(): void
    {
        $class = new ClassDeclaration('MyClass');

        $class->addMethod('__construct')
            ->addPromotedParameter('foo')
            ->setType('string')
            ->setPrivate()
            ->setReadOnly();

        self::assertStringContainsString('private readonly string $foo', $class->render());
    }

    public function testFromElement(): void
    {
        $class = ClassDeclaration::fromElement(new ClassType('Test'));

        self::assertInstanceOf(ClassDeclaration::class, $class);
        self::assertSame('Test', $class->getName());
    }

    public function testGetElement(): void
    {
        $element = (new ClassDeclaration('Test'))->getElement();

        self::assertInstanceOf(ClassType::class, $element);
        self::assertSame('Test', $element->getName());
    }
}
