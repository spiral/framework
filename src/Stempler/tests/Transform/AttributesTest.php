<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\StringLoader;
use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class AttributesTest extends BaseTestCase
{
    public function testAggregatedAttribute(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<a href="" attr:aggregate></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $doc = $builder->load('root');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]->attrs[1]);

        /** @var Aggregate $aggr */
        $aggr = $doc->nodes[0]->attrs[1];

        $this->assertSame('style', $aggr->accepts('style'));
    }

    public function testAggregatedAttributePattern(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<a href="${href}" attr:aggregate="prefix:a-"></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $doc = $builder->load('root');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]->attrs[1]);

        /** @var Aggregate $aggr */
        $aggr = $doc->nodes[0]->attrs[1];

        $this->assertSame(null, $aggr->accepts('style'));
        $this->assertSame('style', $aggr->accepts('a-style'));
    }

    public function testAggregateInclude(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<a href="${href}" attr:aggregate="include:style"></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $doc = $builder->load('root');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]->attrs[1]);

        /** @var Aggregate $aggr */
        $aggr = $doc->nodes[0]->attrs[1];

        $this->assertSame('style', $aggr->accepts('style'));
        $this->assertSame(null, $aggr->accepts('another'));
    }

    public function testAggregateExclude(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<a href="${href}" attr:aggregate="exclude:style"></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $doc = $builder->load('root');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]->attrs[1]);

        /** @var Aggregate $aggr */
        $aggr = $doc->nodes[0]->attrs[1];

        $this->assertSame(null, $aggr->accepts('style'));
        $this->assertSame('another', $aggr->accepts('another'));
    }

    public function testAggregateSimple(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<use:element path="element" as="element"/><element href="google.com" style="color:red"/>'
        );

        $loader->set(
            'element',
            '<a href="${href}" attr:aggregate></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com" style="color:red"></a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testAggregateVoid(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<use:element path="element" as="element"/><element href="google.com" blue/>'
        );
        $loader->set(
            'element',
            '<a href="${href}" attr:aggregate></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com" blue></a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testAggregateBlock(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<use:element path="element" as="element"/>'
            . '<element href="google.com" blue><block:green>orange</block:green></element>'
        );
        $loader->set(
            'element',
            '<a href="${href}" attr:aggregate></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com" blue green="orange"></a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testAggregatePHP(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<use:element path="element" as="element"/>'
            . '<element href="google.com" {!! $value ? "checked" : "" !!}/>'
        );
        $loader->set(
            'element',
            '<a href="${href}" attr:aggregate></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com" <?php echo $value ? "checked" : ""; ?>></a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testAggregateVerbatim(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<use:element path="element" as="element"/><element href="google.com" style="color: <?=\'red\'?>"/>'
        );
        $loader->set(
            'element',
            '<a href="${href}" attr:aggregate></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com" style="color: <?=\'red\'?>"></a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testEqualsToPHP(): void
    {
        $loader ??= new StringLoader();
        $loader->set(
            'root',
            '<use:element path="element" as="element"/><element href="google.com" class=<?=\'red\'?>/>'
        );
        $loader->set(
            'element',
            '<a href="${href}" attr:aggregate></a>'
        );

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com" class=<?=\'red\'?>></a>',
            $builder->compile('root')->getContent()
        );
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);
        $builder->addVisitor(new ResolveImports($builder), Builder::STAGE_TRANSFORM);

        return $builder;
    }

    protected function getVisitors(): array
    {
        return [
            new DefineBlocks(),
            new DefineAttributes()
        ];
    }
}
