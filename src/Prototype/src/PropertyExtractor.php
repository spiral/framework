<?php

declare(strict_types=1);

namespace Spiral\Prototype;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Spiral\Prototype\NodeVisitors\LocateProperties;

/**
 * @internal
 */
final class PropertyExtractor
{
    private readonly Parser $parser;

    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?? (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    /**
     * Get list of all virtual property names.
     */
    public function getPrototypeProperties(string $code): array
    {
        $v = new LocateProperties();

        $tr = new NodeTraverser();
        $tr->addVisitor($v);
        $tr->traverse($this->parser->parse($code));

        return $v->getProperties();
    }
}
