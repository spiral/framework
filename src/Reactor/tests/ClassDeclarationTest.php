<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

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
}
