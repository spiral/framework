<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Stempler\Compiler\Renderer\CoreRenderer;
use Spiral\Stempler\Compiler\Renderer\HTMLRenderer;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;
use Spiral\Tests\Stempler\Compiler\BaseTestCase;
use Spiral\Stempler\Traverser;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

class TraverserTest extends BaseTestCase implements VisitorInterface
{
    protected const RENDERS = [
        CoreRenderer::class,
        HTMLRenderer::class,
    ];

    protected const GRAMMARS = [
        HTMLGrammar::class => HTMLSyntax::class,
    ];

    public function testAlterNode(): void
    {
        $doc = $this->parse('<a href="url"></a>');

        $t = new Traverser();
        $t->addVisitor(new class() implements VisitorInterface {
            public function enterNode(mixed $node, VisitorContext $ctx): mixed
            {
                if ($node instanceof Tag && $node->name === 'a') {
                    $node->name = 'b';
                }

                return null;
            }

            public function leaveNode(mixed $node, VisitorContext $ctx): mixed
            {
                return null;
            }
        });

        $doc->nodes = $t->traverse($doc->nodes);

        self::assertSame('<b href="url"></b>', $this->compile($doc));
    }

    public function testReplaceNode(): void
    {
        $doc = $this->parse('<a href="url"></a>');

        $t = new Traverser();
        $t->addVisitor(new class() implements VisitorInterface {
            public function enterNode(mixed $node, VisitorContext $ctx): mixed
            {
                return null;
            }

            public function leaveNode(mixed $node, VisitorContext $ctx): mixed
            {
                if ($node instanceof Tag && $node->name === 'a') {
                    $new = new Tag();
                    $new->name = 'link';
                    $new->void = true;
                    $new->attrs[] = new Attr('src', $node->attrs[0]->value);
                    return $new;
                }

                return null;
            }
        });

        $doc->nodes = $t->traverse($doc->nodes);

        self::assertSame('<link src="url"/>', $this->compile($doc));
    }

    public function testRemoveNode(): void
    {
        $doc = $this->parse('<a href="url"><b></b>hello</a>');

        $t = new Traverser();
        $t->addVisitor(new class() implements VisitorInterface {
            public function enterNode(mixed $node, VisitorContext $ctx): mixed
            {
                return null;
            }

            public function leaveNode(mixed $node, VisitorContext $ctx): mixed
            {
                if ($node instanceof Tag && $node->name === 'b') {
                    return VisitorInterface::REMOVE_NODE;
                }

                return null;
            }
        });

        $doc->nodes = $t->traverse($doc->nodes);

        self::assertSame('<a href="url">hello</a>', $this->compile($doc));
    }

    public function testVisitorContext(): void
    {
        $doc = $this->parse('<a href="url"><b>hello</b></a>');

        $t = new Traverser();
        $t->addVisitor($this);

        $doc->nodes = $t->traverse($doc->nodes);
        // To suppress Rector error
        self::assertTrue(true);
    }

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        if ($ctx->getCurrentNode() instanceof Raw) {
            self::assertInstanceOf(Tag::class, $ctx->getParentNode());
            self::assertSame('b', $ctx->getParentNode()->name);

            self::assertInstanceOf(Tag::class, $ctx->getFirstNode());
            self::assertSame('a', $ctx->getFirstNode()->name);
        }

        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }
}
