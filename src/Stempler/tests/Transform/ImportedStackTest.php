<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\StringLoader;
use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Finalizer\StackCollector;
use Spiral\Stempler\Transform\Finalizer\TrimRaw;
use Spiral\Stempler\Transform\Merge\ExtendsParent;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;
use Spiral\Stempler\Transform\Visitor\DefineHidden;
use Spiral\Stempler\Transform\Visitor\DefineStacks;

class ImportedStackTest extends BaseTestCase
{
    public function testEmptyStack(): void
    {
        $doc = $this->parse('<stack:collect name="css"/>');

        self::assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        self::assertSame([], $doc->nodes[0]->nodes);
    }

    public function testImportedStack(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/>
<stack:collect name="css"/>
<url href="google.com">hello world</url>
'
        );
        $loader->set('import', '
<stack:push name="css">css</stack:push>
<a href="${href}"><block:context/></a>
');

        $builder = $this->getBuilder($loader, []);

        self::assertSame('css<a href="google.com">hello world</a>', $builder->compile('root')->getContent());
    }

    public function testStackDefinedInParent(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<extends:parent/>
<use:element path="import" as="url"/>
<block:body>
    <url href="google.com">hello world</url>
</block:body>
'
        );

        $loader->set(
            'parent',
            '<html>
            <stack:collect name="css"/>
            <body>${body}</body>
            <stack:collect name="js"/>
            </html>'
        );

        $loader->set('import', '
<stack:push name="css">css</stack:push>
<a href="${href}"><block:context/></a>
');

        $builder = $this->getBuilder($loader, []);

        self::assertSame('<html>css<body><a href="google.com">hello world</a></body></html>', $builder->compile('root')->getContent());
    }

    public function testStackDefinedInParentWithChild(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<extends:parent/>
<use:element path="import" as="url"/>

<block:body>
    <stack:push name="js">js</stack:push>
    <url href="google.com">hello world</url>
</block:body>
'
        );

        $loader->set(
            'parent',
            '<html>
            <stack:collect name="css"/>
            <body>${body}</body>
            <stack:collect name="js"/>
            </html>'
        );

        $loader->set('import', '
<stack:push name="css">css</stack:push>
<a href="${href}"><block:context/></a>
');

        $builder = $this->getBuilder($loader, []);

        self::assertSame('<html>css<body><a href="google.com">hello world</a></body>js</html>', $builder->compile('root')->getContent());
    }

    public function testGrid(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<use:dir dir="grid" ns="grid"/>
<grid:render>
    some arbitrary text

    <grid:cell title="ID">value</grid:cell>
    <grid:cell title="Title">value</grid:cell>
</grid:render>
'
        );

        $loader->set(
            'grid' . DIRECTORY_SEPARATOR . 'render',
            '
<table>
<thead>
<stack:collect name="head" level="1"/>
</thead>
<tbody>
<stack:collect name="body" level="1"/>
</tbody>
<hidden>${context}</hidden>
</table>
'
        );

        $loader->set(
            'grid' . DIRECTORY_SEPARATOR . 'cell',
            '
<stack:push name="head"><tr>${title}</tr></stack:push>
<stack:push name="body"><td>${context}</td></stack:push>
'
        );

        $builder = $this->getBuilder($loader, []);

        self::assertSame('<table><thead><tr>ID</tr><tr>Title</tr></thead><tbody><td>value</td><td>value</td></tbody></table>', $builder->compile('root')->getContent());
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);

        // import resolution
        $builder->addVisitor(new ResolveImports($builder), Builder::STAGE_TRANSFORM);
        $builder->addVisitor(new ExtendsParent($builder), Builder::STAGE_TRANSFORM);

        // so we can inject into PHP
        $dynamic = new DynamicToPHP();
        $dynamic->addDirective(new LoopDirective());

        $builder->addVisitor($dynamic);
        $builder->addVisitor(new StackCollector(), Builder::STAGE_FINALIZE);
        $builder->addVisitor(new TrimRaw(), Builder::STAGE_FINALIZE);

        return $builder;
    }

    protected function getVisitors(): array
    {
        return [
            new DefineAttributes(),
            new DefineBlocks(),
            new DefineStacks(),
            new DefineHidden()
        ];
    }
}
