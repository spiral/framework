<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\StringLoader;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Merge\ExtendsParent;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class ExtendsTest extends BaseTestCase
{
    public function testNoExtends(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<block:content>hello world</block:content>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            'hello world',
            $builder->compile('root')->getContent()
        );
    }

    public function testExtendsParent(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<extends:parent/><block:content>hello world</block:content>');
        $loader->set('parent', '<b><block:content>parent world</block:content></b>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<b>hello world</b>',
            $builder->compile('root')->getContent()
        );
    }

    public function testExtendsParentBlock(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<extends:parent/><block:content>hello ${parent}</block:content>');
        $loader->set('parent', '<b><block:content>parent world</block:content></b>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<b>hello parent world</b>',
            $builder->compile('root')->getContent()
        );
    }

    public function testExtendsParentBlockWithNoAttribute(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<extends:parent/><block:content>hello ${parent}</block:content>');
        $loader->set('parent', '<b attr:aggregate><block:content>parent world</block:content></b>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<b>hello parent world</b>',
            $builder->compile('root')->getContent()
        );
    }

    public function testExtendsParentBlockWithAttribute(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<extends:parent class="red"/><block:content>hello ${parent}</block:content>');
        $loader->set('parent', '<b attr:aggregate><block:content>parent world</block:content></b>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<b class="red">hello parent world</b>',
            $builder->compile('root')->getContent()
        );
    }

    public function testExtendsAttribute(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<extends:parent value="x"/><block:content>hello ${parent}</block:content>');
        $loader->set('parent', '<b value="${value}"><block:content>parent world</block:content></b>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<b value="x">hello parent world</b>',
            $builder->compile('root')->getContent()
        );
    }

    public function testExtendsViaPath(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<extends path="parent" value="x"/><block:content>hello ${parent}</block:content>');
        $loader->set('parent', '<b value="${value}"><block:content>parent world</block:content></b>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<b value="x">hello parent world</b>',
            $builder->compile('root')->getContent()
        );
    }

    public function testExtendsMultiple(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', '<extends:child/><block:data>root</block:data>');
        $loader->set('child', '<extends:parent/><block:content><i><block:data/></i></block:content>');
        $loader->set('parent', '<b><block:content/></b>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<b><i>root</i></b>',
            $builder->compile('root')->getContent()
        );
    }

    public function testExtendsInline(): void
    {
        $loader ??= new StringLoader();
        $loader->set('root', 'hello <placeholder><extends:child/><block:data>root</block:data></placeholder>');
        $loader->set('child', '<extends:parent/><block:content><i><block:data/></i></block:content>');
        $loader->set('parent', '<b><block:content/></b>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            'hello <b><i>root</i></b>',
            $builder->compile('root')->getContent()
        );
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
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
