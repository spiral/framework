<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Tests\Stempler\Transform\BaseTestCase;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Merge\ExtendsParent;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class SourcemapTest extends BaseTestCase
{
    public function testSimpleLoad(): void
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('bundle-import');

        self::assertSame('<a href="abc">cde</a>', trim($res->getContent()));
    }

    public function testGetTemplates(): void
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('bundle-import');

        self::assertSame([
            'bundle-import',
            'import/bundle',
        ], $res->getPaths());
    }

    public function testPHPImportResult(): void
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('import-php');

        self::assertSame(preg_replace("/\s+/", '', '
<div>
    <?php foreach ([\'a\', \'b\', \'c\'] as $value): ?>
    <b><?php echo htmlspecialchars((string) ($value), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?></b>
    <?php endforeach; ?>
</div>'), preg_replace("/\s+/", '', $res->getContent()));
    }

    public function testCompress(): void
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('import-php');

        $sm = $res->getSourceMap($this->getFixtureLoader());

        $sm2 = unserialize(serialize($sm));

        self::assertEquals($sm, $sm2);
    }

    public function testGetStack(): void
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('import-php');

        $sm = $res->getSourceMap($this->getFixtureLoader());

        $stack = $sm->getStack(6);
        self::assertCount(4, $stack);
    }

    public function testTripeImportAndExtend(): void
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('demo-import');

        $sm = $res->getSourceMap($this->getFixtureLoader());

        $stack = $sm->getStack(12);
        self::assertCount(5, $stack);
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
            new DefineBlocks(),
        ];
    }
}
