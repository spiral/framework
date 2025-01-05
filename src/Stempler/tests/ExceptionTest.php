<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Exception\DirectiveException;
use Spiral\Stempler\Exception\ExtendsException;
use Spiral\Stempler\Exception\ImportException;
use Spiral\Stempler\Exception\LoaderException;
use Spiral\Stempler\Exception\ParserException;
use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Tests\Stempler\Transform\BaseTestCase;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Merge\ExtendsParent;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class ExceptionTest extends BaseTestCase
{
    public function testSimpleLoad(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        self::assertSame('hello world', $builder->compile('hello')->getContent());
    }

    public function testSyntaxException(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('broken')->getContent();
        } catch (ParserException $e) {
            self::assertInstanceOf(SyntaxException::class, $e->getPrevious());
            self::assertStringContainsString('broken.dark.php', $e->getFile());
            self::assertSame(3, $e->getLine());
        }
    }

    public function testExceptionInElementImport(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('import/bad-element');
        } catch (ImportException $e) {
            self::assertStringContainsString('bad-element.dark.php', $e->getFile());
            self::assertSame(1, $e->getLine());
        }
    }

    public function testExceptionInElementImport3rdLine(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('import/bad-element-3');
        } catch (ImportException $e) {
            self::assertStringContainsString('bad-element-3.dark.php', $e->getFile());
            self::assertSame(3, $e->getLine());
        }
    }

    public function testExceptionInDirImport(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('import/bad-dir');
        } catch (ImportException $e) {
            self::assertStringContainsString('bad-dir.dark.php', $e->getFile());
            self::assertSame(1, $e->getLine());
        }
    }

    public function testExceptionInDirImport2(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('import/bad-dir-2');
        } catch (ImportException $e) {
            self::assertStringContainsString('bad-dir-2.dark.php', $e->getFile());
            self::assertSame(2, $e->getLine());
        }
    }

    public function testDirectiveException(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('bad-directive');
        } catch (DirectiveException $e) {
            self::assertStringContainsString('bad-directive.dark.php', $e->getFile());
            self::assertSame(2, $e->getLine());
        }
    }

    public function testExceptionInImport(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('exception-in-import');
        } catch (ImportException $e) {
            self::assertStringContainsString('exception-in-import.dark.php', $e->getFile());
            self::assertSame(3, $e->getLine());

            $e = $e->getPrevious();
            self::assertInstanceOf(ImportException::class, $e);
            self::assertStringContainsString('bad-element.dark.php', $e->getFile());
            self::assertSame(1, $e->getLine());
        }
    }

    public function testSyntaxExceptionInImport(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('exception-in-import-2');
        } catch (ImportException $e) {
            self::assertInstanceOf(ImportException::class, $e);
            self::assertStringContainsString('exception-in-import-2.dark.php', $e->getFile());
            self::assertSame(3, $e->getLine());

            $e = $e->getPrevious();
            self::assertInstanceOf(ParserException::class, $e);
            self::assertStringContainsString('bundle2.dark.php', $e->getFile());
            self::assertSame(3, $e->getLine());
        }
    }

    public function testBadExtends(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('bad-extends');
        } catch (ExtendsException $e) {
            self::assertStringContainsString('bad-extends.dark.php', $e->getFile());
            self::assertSame(1, $e->getLine());

            self::assertInstanceOf(LoaderException::class, $e->getPrevious());
        }
    }

    public function testBadExtendsDueToSyntax(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('bad-extends-2');
        } catch (ExtendsException $e) {
            self::assertInstanceOf(ExtendsException::class, $e);
            self::assertStringContainsString('bad-extends-2.dark.php', $e->getFile());
            self::assertSame(1, $e->getLine());

            $e = $e->getPrevious();
            self::assertInstanceOf(ParserException::class, $e);
            self::assertStringContainsString('broken.dark.php', $e->getFile());
            self::assertSame(3, $e->getLine());
        }
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors = []): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);
        $builder->addVisitor(new ResolveImports($builder), Builder::STAGE_TRANSFORM);
        $builder->addVisitor(new ExtendsParent($builder), Builder::STAGE_TRANSFORM);

        // so we can inject into PHP
        $dynamic = new DynamicToPHP();
        $dynamic->addDirective(new LoopDirective());

        $builder->addVisitor($dynamic, Builder::STAGE_FINALIZE);

        return $builder;
    }

    protected function getVisitors(): array
    {
        return [
            new DefineAttributes(),
            new DefineBlocks()
        ];
    }
}
