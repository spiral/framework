<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype;

use PhpParser\Lexer\Emulative;
use PhpParser\Parser\Php7;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Spiral\Prototype\NodeVisitors\AddProperty;
use Spiral\Prototype\NodeVisitors\AddUse;
use Spiral\Prototype\NodeVisitors\DefineConstructor;
use Spiral\Prototype\NodeVisitors\RemoveTrait;
use Spiral\Prototype\NodeVisitors\RemoveUse;
use Spiral\Prototype\NodeVisitors\UpdateConstructor;

/**
 * Injects needed class dependencies into given source code.
 */
final class Injector
{
    private Parser $parser;

    private Lexer $lexer;

    /** @var null|Standard|PrettyPrinterAbstract */
    private $printer;

    private NodeTraverser $cloner;

    /**
     * @param Lexer|null                 $lexer
     * @param PrettyPrinterAbstract|null $printer
     */
    public function __construct(Lexer $lexer = null, PrettyPrinterAbstract $printer = null)
    {
        if ($lexer === null) {
            $lexer = new Emulative([
                'usedAttributes' => [
                    'comments',
                    'startLine',
                    'endLine',
                    'startTokenPos',
                    'endTokenPos',
                ],
            ]);
        }

        $this->lexer = $lexer;
        $this->parser = new Php7($this->lexer);

        $this->cloner = new NodeTraverser();
        $this->cloner->addVisitor(new CloningVisitor());

        $this->printer = $printer ?? new Standard();
    }

    /**
     * Inject dependencies into PHP Class source code. Attention, resulted code will attempt to
     * preserve formatting but will affect it. Do not forget to add formatting fixer.
     */
    public function injectDependencies(
        string $code,
        ClassNode $node,
        bool $removeTrait = false,
        bool $useTypedProperties = false,
        bool $noPhpDoc = false
    ): string {
        if (empty($node->dependencies)) {
            if ($removeTrait) {
                $tr = new NodeTraverser();
                $tr->addVisitor(new RemoveUse());
                $tr->addVisitor(new RemoveTrait());

                return $this->traverse($code, $tr);
            }

            return $code;
        }

        $tr = new NodeTraverser();
        $tr->addVisitor(new AddUse($node));

        if ($removeTrait) {
            $tr->addVisitor(new RemoveUse());
            $tr->addVisitor(new RemoveTrait());
        }

        $tr->addVisitor(new AddProperty($node, $useTypedProperties, $noPhpDoc));
        $tr->addVisitor(new DefineConstructor());
        $tr->addVisitor(new UpdateConstructor($node));

        return $this->traverse($code, $tr);
    }

    private function traverse(string $code, NodeTraverser $tr): string
    {
        $nodes = $this->parser->parse($code);
        $tokens = $this->lexer->getTokens();

        $output = $tr->traverse($this->cloner->traverse($nodes));

        return $this->printer->printFormatPreserving($output, $nodes, $tokens);
    }
}
