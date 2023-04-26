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

class ImportBundleTest extends BaseTestCase
{
    public function testNoImport(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set('root', '<url href="google.com">hello world</url>');
        $loader->set('import', '<a href="${href}">${context}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<url href="google.com">hello world</url>',
            $builder->compile('root')->getContent()
        );
    }

    public function testInlineBundle(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set('root', '
<use:bundle path="bundle" ns="prefix"/>
<prefix:url href="google.com">hello world</prefix:url>
');

        $loader->set('bundle', '
<use:inline name="url">
    <a href="${href}">${context}</a>
</use:inline>
');

        $loader->set('import', '<a href="${href}">${context}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com">hello world</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testImportElementViaBundle(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set('root', '
<use:bundle path="bundle" ns="prefix"/>
<prefix:url href="google.com">hello world</prefix:url>
');

        $loader->set('bundle', '<use:element path="import" as="url"/>');
        $loader->set('import', '<a href="${href}">${context}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com">hello world</a>',
            $builder->compile('root')->getContent()
        );
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
