<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\StringLoader;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Finalizer\TrimRaw;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class ImportInlineTest extends BaseTestCase
{
    public function testNoImport(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<url href="google.com">hello world</url>');
        $loader->set('import', '<a href="${href}">${context}</a>');

        $builder = $this->getBuilder($loader, []);

        self::assertSame('<url href="google.com">hello world</url>', $builder->compile('root')->getContent());
    }

    public function testInlineImport(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '
<use:inline name="url">
    <a href="${href}">${context}</a>
</use:inline>
<url href="google.com">hello world</url>
');

        $builder = $this->getBuilder($loader, []);

        self::assertSame('<a href="google.com">hello world</a>', $builder->compile('root')->getContent());
    }

    public function testInlineImportN(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '
<use:inline name="url">
    <a href="${href}">${context}</a>
</use:inline>
<url href="google.com">hello world</url>
<url href="spiralscout.com">foo bar</url>
');

        $builder = $this->getBuilder($loader, []);

        self::assertSame('<a href="google.com">hello world</a><a href="spiralscout.com">foo bar</a>', $builder->compile('root')->getContent());
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);

        // import resolution
        $builder->addVisitor(new ResolveImports($builder), Builder::STAGE_TRANSFORM);

        // so we can inject into PHP
        $dynamic = new DynamicToPHP();
        $dynamic->addDirective(new LoopDirective());

        $builder->addVisitor($dynamic);
        $builder->addVisitor(new TrimRaw(), Builder::STAGE_FINALIZE);

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
