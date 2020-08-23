<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Reactor\AbstractDeclaration;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DeclarationInterface;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\NamespaceDeclaration;
use Spiral\Reactor\Partial;
use Spiral\Reactor\Serializer;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\NamedTrait;
use Spiral\Tests\Reactor\Fixture\DependedElement;

class DeclarationsTest extends TestCase
{
    //Simple test which touches a lot of methods
    public function testClassDeclaration(): ClassDeclaration
    {
        $declaration = new ClassDeclaration('MyClass');
        $declaration->setExtends('Record');
        $this->assertSame('Record', $declaration->getExtends());

        $declaration->addInterface('Traversable');
        $this->assertSame(['Traversable'], $declaration->getInterfaces());

        $this->assertTrue($declaration->hasInterface('Traversable'));
        $declaration->removeInterface('Traversable');
        $this->assertSame([], $declaration->getInterfaces());

        $declaration->constant('BOOT')
            ->setValue(true)
            ->setComment('Always boot');

        $this->assertTrue($declaration->getConstants()->has('BOOT'));
        $this->assertTrue($declaration->getConstants()->get('BOOT')->getValue());

        $declaration->property('names')
            ->setAccess(Partial\Property::ACCESS_PRIVATE)
            ->setComment(['This is names', '', '@var array'])
            ->setDefaultValue(['Anton', 'John']);

        $this->assertTrue($declaration->getProperties()->has('names'));
        $this->assertSame(
            ['Anton', 'John'],
            $declaration->getProperties()->get('names')->getDefaultValue()
        );

        $method = $declaration->method('sample');
        $method->parameter('input')->setType('int');
        $method->parameter('output')->setType('int')->setDefaultValue(null)->setPBR(true);
        $method->setAccess(Partial\Method::ACCESS_PUBLIC)->setStatic(true);

        $method->setSource([
            '$output = $input;',
            'return true;'
        ]);

        $this->assertSame(
            preg_replace('/\s+/', '', 'class MyClass extends Record
            {
                /**
                 * Always boot
                 */
                private const BOOT = true;

                /**
                 * This is names
                 *
                 * @var array
                 */
                private $names = [
                    \'Anton\',
                    \'John\'
                ];

                public static function sample(int $input, int &$output = null)
                {
                    $output = $input;
                    return true;
                }
            }'),
            preg_replace('/\s+/', '', $declaration->render())
        );

        return $declaration;
    }

    public function testFileDeclaration(): void
    {
        $declaration = new FileDeclaration('Spiral\\Custom_Namespace', 'This is test file');
        $declaration->addUse(ContainerInterface::class, 'Container');

        $this->assertSame('Spiral\\Custom_Namespace', $declaration->getNamespace());

        $declaration->addElement($this->testClassDeclaration());

        $this->assertSame(
            preg_replace('/\s+/', '', '
            <?php
            /**
             * This is test file
             */
             namespace Spiral\\Custom_Namespace;

             use Psr\Container\ContainerInterface as Container;

             class MyClass extends Record
             {
                 /**
                  * Always boot
                  */
                 private const BOOT = true;

                 /**
                  * This is names
                  *
                  * @var array
                  */
                 private $names = [
                     \'Anton\',
                     \'John\'
                 ];

                 public static function sample(int $input, int &$output = null)
                 {
                     $output = $input;
                     return true;
                 }
             }'),
            preg_replace('/\s+/', '', (string)$declaration)
        );
    }

    public function testDependedFileImport(): void
    {
        $declaration = new FileDeclaration('Spiral\\Custom_Namespace', 'This is test file');
        $declaration->addElement(new DependedElement('depended'));

        $this->assertStringContainsString(
            preg_replace('/\s+/', '', 'use Spiral\Reactor\DependedInterface as DependencyAlias;'),
            preg_replace('/\s+/', '', (string)$declaration)
        );
    }

    public function testDependedNamespaceImport(): void
    {
        $declaration = new NamespaceDeclaration('Custom_Namespace');
        $declaration->addElement(new DependedElement('depended'));

        $this->assertStringContainsString(
            preg_replace('/\s+/', '', 'use Spiral\Reactor\DependedInterface as DependencyAlias;'),
            preg_replace('/\s+/', '', $declaration->render())
        );
    }

    public function testNamespaceDeclaration(): void
    {
        $declaration = new NamespaceDeclaration('Spiral\\Custom_Namespace', 'This is test namespace');
        $declaration->addUse(ContainerInterface::class, 'Container');

        $declaration->addElement($this->testClassDeclaration());

        $this->assertSame(
            preg_replace('/\s+/', '', '
            /**
             * This is test namespace
             */
             namespace Spiral\\Custom_Namespace {
                 use Psr\Container\ContainerInterface as Container;

                 class MyClass extends Record
                 {
                     /**
                      * Always boot
                      */
                     private const BOOT = true;

                     /**
                      * This is names
                      *
                      * @var array
                      */
                     private $names = [
                         \'Anton\',
                         \'John\'
                     ];

                     public static function sample(int $input, int &$output = null)
                     {
                         $output = $input;
                         return true;
                     }
                 }
             }'),
            preg_replace('/\s+/', '', $declaration->render())
        );
    }

    public function testFileDeclaration2(): void
    {
        $f = new FileDeclaration();
        $f->setNamespace('Spiral\\Test');
        $this->assertStringContainsString('namespace Spiral\\Test;', $f->render());

        $c = new ClassDeclaration('TestClass');
        $c->addTrait(NamedTrait::class);

        $f->addElement($c);
        $this->assertTrue($f->getElements()->has('TestClass'));
        $this->assertStringContainsString('use Spiral\\Reactor\\Traits\\NamedTrait;', $f->render());
    }

    public function testFileDeclarationDirectives(): void
    {
        $f = new FileDeclaration();
        $this->assertStringNotContainsString('declare', $f->render());

        $f->setDirectives();
        $this->assertStringNotContainsString('declare', $f->render());

        $f->setDirectives('strict_types=1', 'ticks=1');
        $this->assertStringContainsString('declare', $f->render());
        $this->assertStringContainsString('strict_types=1', $f->render());
        $this->assertStringContainsString('ticks=1', $f->render());
    }

    public function testNamespaceDeclaration2(): void
    {
        $f = new NamespaceDeclaration('Spiral\\Test');

        $c = new ClassDeclaration('TestClass', AbstractDeclaration::class, [
            DeclarationInterface::class
        ]);

        $c->addTrait(NamedTrait::class);
        $this->assertCount(1, $c->getTraits());

        $c->setTraits([CommentTrait::class]);
        $this->assertCount(1, $c->getTraits());

        $this->assertCount(0, $c->getMethods());
        $f->addElement($c);

        $this->assertTrue($f->getElements()->has('TestClass'));
        $this->assertStringContainsString('use Spiral\\Reactor\\Traits\\CommentTrait;', $f->render());

        $c->removeTrait(CommentTrait::class);
        $this->assertStringNotContainsString('use Spiral\\Reactor\\Traits\\CommentTrait;', $f->render());

        $c->setComment('hello world');
        $this->assertStringContainsString('hello world', $f->render());
    }

    public function testUses(): void
    {
        $f = new NamespaceDeclaration('Spiral\\Test');
        $f->addUse(AbstractDeclaration::class);
        $f->addUse(NamedInterface::class, 'Named');

        $this->assertTrue($f->uses(AbstractDeclaration::class));
        $this->assertTrue($f->uses(NamedInterface::class));

        $this->assertStringContainsString("use Spiral\Reactor\AbstractDeclaration;", $f->render());
        $this->assertStringContainsString("use Spiral\Reactor\NamedInterface as Named;", $f->render());

        $f->removeUse(NamedInterface::class);
        $this->assertStringContainsString("use Spiral\Reactor\AbstractDeclaration;", $f->render());
        $this->assertStringNotContainsString("use Spiral\Reactor\NamedInterface as Named;", $f->render());

        $f->addUses([
            NamedInterface::class => 'Named',
            Serializer::class     => null
        ]);

        $this->assertStringContainsString("use Spiral\Reactor\AbstractDeclaration;", $f->render());
        $this->assertStringContainsString("use Spiral\Reactor\Serializer;", $f->render());
        $this->assertStringContainsString("use Spiral\Reactor\NamedInterface as Named;", $f->render());

        $f->setUses([
            NamedInterface::class => 'Named',
            Serializer::class     => null
        ]);

        $this->assertStringNotContainsString("use Spiral\Reactor\AbstractDeclaration;", $f->render());
        $this->assertStringContainsString("use Spiral\Reactor\Serializer;", $f->render());
        $this->assertStringContainsString("use Spiral\Reactor\NamedInterface as Named;", $f->render());
    }

    public function testComment(): void
    {
        $c = new ClassDeclaration('TestClass', AbstractDeclaration::class, [
            DeclarationInterface::class
        ]);

        $c->setComment('');
        $this->assertStringNotContainsString('/**', $c->render());

        $c->setComment("/**\nhello world\n*/");
        $this->assertStringContainsString('hello world', $c->render());
    }

    public function testCommentLines(): void
    {
        $comment = new Partial\Comment();
        $comment->setString("/**\n * hello world\n/*\n *add space\n*/\n *    indent 3 spaces\n */");
        $this->assertStringContainsString(
            "/**\n * hello world\n * add space\n *    indent 3 spaces\n */",
            $comment->render()
        );
    }
}
