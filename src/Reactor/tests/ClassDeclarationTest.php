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

        $this->assertSame(preg_replace('/\s+/', '', '/**
            * Description of class.
            * Second line
            *
            * @property-read Nette\Forms\Form $form
            */
            final class MyClass extends Spiral\Tests\Reactor\ClassDeclarationTest implements Countable
            {
            }'),
            preg_replace('/\s+/', '', (string) $declaration)
        );
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

        $this->assertSame(preg_replace('/\s+/', '', '
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
            }'),
            preg_replace('/\s+/', '', (string) $declaration)
        );
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

        $this->assertSame(preg_replace('/\s+/', '', '
        class MyClass extends Spiral\Tests\Reactor\ClassDeclarationTest implements Countable
        {
           /**
            * Count it.
            */
            final protected function count(array &$items = []): ?int
            {
                return count($items ?: $this->items);
            }
        }'),
            preg_replace('/\s+/', '', (string) $declaration)
        );
    }

    public function testName(): void
    {
        $class = new ClassDeclaration();

        $this->assertNull($class->getName());

        $class->setName('test');
        $this->assertSame('Test', $class->getName()); // classified

        $class->setName('Test');
        $this->assertSame('Test', $class->getName());

        $class->setName(null);
        $this->assertNull($class->getName());
    }

    public function testFinal(): void
    {
        $class = new ClassDeclaration();

        $this->assertFalse($class->isFinal());

        $class->setFinal();
        $this->assertTrue($class->isFinal());

        $class->setFinal(false);
        $this->assertFalse($class->isFinal());

        $class->setFinal(true);
        $this->assertTrue($class->isFinal());
    }

    public function testAbstract(): void
    {
        $class = new ClassDeclaration();

        $this->assertFalse($class->isAbstract());

        $class->setAbstract();
        $this->assertTrue($class->isAbstract());

        $class->setAbstract(false);
        $this->assertFalse($class->isAbstract());

        $class->setAbstract(true);
        $this->assertTrue($class->isAbstract());
    }

    public function testExtends(): void
    {
        $class = new ClassDeclaration();

        $this->assertNull($class->getExtends());

        $class->setExtends('Test');
        $this->assertSame('Test', $class->getExtends());
    }

    public function testImplements(): void
    {
        $class = new ClassDeclaration();

        $this->assertEmpty($class->getImplements());

        $class->addImplement('Test');
        $this->assertSame(['Test'], $class->getImplements());

        $class->setImplements(['Foo', 'Bar']);
        $this->assertSame(['Foo', 'Bar'], $class->getImplements());

        $class->removeImplement('Bar');
        $this->assertSame(['Foo'], $class->getImplements());

        $class->removeImplement('Foo');
        $this->assertEmpty($class->getImplements());
    }

    public function testAddMember(): void
    {
        $class = new ClassDeclaration();

        $this->assertEmpty($class->getConstants());
        $class->addMember(new Partial\Constant('TEST'));
        $this->assertCount(1, $class->getConstants());
        $this->assertInstanceOf(Partial\Constant::class, $class->getConstant('TEST'));

        $this->assertEmpty($class->getMethods());
        $class->addMember(new Partial\Method('test'));
        $this->assertCount(1, $class->getMethods());
        $this->assertInstanceOf(Partial\Method::class, $class->getMethod('test'));

        $this->assertEmpty($class->getProperties());
        $class->addMember(new Partial\Property('test'));
        $this->assertCount(1, $class->getProperties());
        $this->assertInstanceOf(Partial\Property::class, $class->getProperty('test'));

        $this->assertEmpty($class->getTraits());
        $class->addMember(new Partial\TraitUse('test'));
        $this->assertCount(1, $class->getTraits());
        $this->assertInstanceOf(Partial\TraitUse::class, $class->getTrait('test'));
    }

    public function testIsClass(): void
    {
        $class = new ClassDeclaration();

        $this->assertTrue($class->isClass());

        $this->assertFalse($class->isInterface());
        $this->assertFalse($class->isEnum());
        $this->assertFalse($class->isTrait());
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

        $this->assertSame($expect, preg_replace('/\s+/', '', $class->render()));
        $this->assertSame($expect, preg_replace('/\s+/', '', $class->__toString()));
    }

    public function testFromElement(): void
    {
        $class = ClassDeclaration::fromElement(new ClassType('Test'));

        $this->assertInstanceOf(ClassDeclaration::class, $class);
        $this->assertSame('Test', $class->getName());
    }

    public function testGetElement(): void
    {
        $element = (new ClassDeclaration('Test'))->getElement();

        $this->assertInstanceOf(ClassType::class, $element);
        $this->assertSame('Test', $element->getName());
    }
}
