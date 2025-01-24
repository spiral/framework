<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Transform\Finalizer\StackCollector;
use Spiral\Stempler\Transform\Finalizer\TrimRaw;
use Spiral\Stempler\Transform\Visitor\DefineStacks;

class StackTest extends BaseTestCase
{
    public function testEmptyStack(): void
    {
        $doc = $this->parse('<stack:collect name="css"/>');

        self::assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        self::assertSame([], $doc->nodes[0]->nodes);
    }

    public function testDefaultStack(): void
    {
        $doc = $this->parse('<stack:collect name="css">css</stack:collect>');

        self::assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        self::assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        self::assertSame('css', $doc->nodes[0]->nodes[0]->content);
    }

    public function testStackPushAfter(): void
    {
        $doc = $this->parse('<stack:collect name="css"/><stack:push name="css">css</stack:push>');

        self::assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        self::assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        self::assertSame('css', $doc->nodes[0]->nodes[0]->content);
    }

    public function testStackPushAfterOrder(): void
    {
        $doc = $this->parse(
            '<stack:collect name="css"/><stack:push name="css">css</stack:push>'
            . '<stack:push name="css">css2</stack:push>',
        );

        self::assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        self::assertCount(1, $doc->nodes);

        self::assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        self::assertSame('css', $doc->nodes[0]->nodes[0]->content);

        self::assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[1]);
        self::assertSame('css2', $doc->nodes[0]->nodes[1]->content);
    }

    public function testPushBefore(): void
    {
        $doc = $this->parse(
            '<stack:push name="css">css2</stack:push><stack:collect name="css"/>'
            . '<stack:push name="css">css</stack:push>',
        );

        self::assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        self::assertCount(1, $doc->nodes);

        self::assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        self::assertSame('css2', $doc->nodes[0]->nodes[0]->content);

        self::assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[1]);
        self::assertSame('css', $doc->nodes[0]->nodes[1]->content);
    }

    public function testPrepend(): void
    {
        $doc = $this->parse(
            '<stack:push name="css">css2</stack:push><stack:collect name="css"/>'
            . '<stack:prepend name="css">css</stack:prepend>',
        );

        self::assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        self::assertCount(1, $doc->nodes);

        self::assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        self::assertSame('css', $doc->nodes[0]->nodes[0]->content);

        self::assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[1]);
        self::assertSame('css2', $doc->nodes[0]->nodes[1]->content);
    }

    public function testPushFromTheSubtag(): void
    {
        $doc = $this->parse(
            '
        <div><stack:push name="css">css2</stack:push></div>
        <stack:collect name="css"/>
        <div><stack:prepend name="css">css</stack:prepend></div>
        ',
        );

        self::assertInstanceOf(Aggregate::class, $doc->nodes[1]);
        self::assertCount(3, $doc->nodes);

        self::assertInstanceOf(Raw::class, $doc->nodes[1]->nodes[0]);
        self::assertSame('css', $doc->nodes[1]->nodes[0]->content);

        self::assertInstanceOf(Raw::class, $doc->nodes[1]->nodes[1]);
        self::assertSame('css2', $doc->nodes[1]->nodes[1]->content);
    }

    public function testPushIntoSubtagOutofScope(): void
    {
        self::assertSame('<div></div><stack:push name="css">css2</stack:push>', $this->compile(
            '<div><stack:collect name="css"/></div>
            <stack:push name="css">css2</stack:push>',
        )->getContent());
    }

    public function testPushIntoSubtagInTheScope(): void
    {
        self::assertSame('<div>css2</div>', $this->compile(
            '
            <div><stack:collect name="css" level="1"/></div>
            <stack:push name="css">css2</stack:push>
            ',
        )->getContent());
    }

    public function testMultipleScopes(): void
    {
        self::assertSame('css2<div>css1</div>', $this->compile(
            '
<stack:collect name="css"/>
<div>
    <stack:collect name="css"/>
    <stack:push name="css">css1</stack:push>
</div>
<stack:push name="css">css2</stack:push>',
        )->getContent());
    }

    public function testScopeOverlap1(): void
    {
        self::assertSame('<div><div>css1</div></div><stack:push name="css">css2</stack:push>', $this->compile(
            '
<div>
    <div>
        <stack:collect name="css" level="1"/>
    </div>
    <stack:push name="css">css1</stack:push>
</div>
<stack:push name="css">css2</stack:push>',
        )->getContent());
    }

    public function testScopeOverlap2(): void
    {
        self::assertSame('<div><div>css1css2</div></div>', $this->compile(
            '
<div>
    <div>
        <stack:collect name="css" level="2"/>
    </div>
    <stack:push name="css">css1</stack:push>
</div>
<stack:push name="css">css2</stack:push>',
        )->getContent());
    }

    public function testNoUniquness(): void
    {
        self::assertSame('123', $this->compile(
            '
<stack:collect name="element" level="2"/>
<stack:push name="element">1</stack:push>
<stack:push name="element">2</stack:push>
<stack:push name="element">3</stack:push>
',
        )->getContent());
    }

    public function testUniquness(): void
    {
        self::assertSame('13', $this->compile(
            '
<stack:collect name="element" level="2"/>
<stack:push name="element" unique-id="1">1</stack:push>
<stack:push name="element" unique-id="1">2</stack:push>
<stack:push name="element" unique-id="2">3</stack:push>
',
        )->getContent());
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);

        // import resolution
        $builder->addVisitor(new StackCollector(), Builder::STAGE_FINALIZE);
        $builder->addVisitor(new TrimRaw(), Builder::STAGE_FINALIZE);

        return $builder;
    }

    protected function getVisitors(): array
    {
        return [
            new DefineStacks(),
        ];
    }
}
