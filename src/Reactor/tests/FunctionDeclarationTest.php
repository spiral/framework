<?php

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use Nette\PhpGenerator\GlobalFunction;
use PHPUnit\Framework\TestCase;
use Spiral\Reactor\FunctionDeclaration;

final class FunctionDeclarationTest extends TestCase
{
    public function testFrom(): void
    {
        $fn = FunctionDeclaration::from('var_dump');

        self::assertInstanceOf(FunctionDeclaration::class, $fn);
        self::assertSame('var_dump', $fn->getName());
        self::assertSame('void', $fn->getReturnType());
        self::assertCount(2, $fn->getParameters());
    }

    public function testRender(): void
    {
        $expect = \preg_replace(
            '/\s+/',
            '',
            '
           /**
            * Some function
            */
            #[SomeAttribute]
            function test(): string
            {
                return \'Hello world\';
            }',
        );

        $fn = new FunctionDeclaration('test');
        $fn
            ->addBody('return \'Hello world\';')
            ->setReturnType('string')
            ->addAttribute('SomeAttribute')
            ->addComment('Some function');

        self::assertSame($expect, \preg_replace('/\s+/', '', $fn->render()));
        self::assertSame($expect, \preg_replace('/\s+/', '', $fn->__toString()));
    }

    public function testFromElement(): void
    {
        $fn = FunctionDeclaration::fromElement(new GlobalFunction('var_dump'));

        self::assertInstanceOf(FunctionDeclaration::class, $fn);
    }

    public function testGetElement(): void
    {
        $element = (new FunctionDeclaration('var_dump'))->getElement();

        self::assertInstanceOf(GlobalFunction::class, $element);
    }
}
