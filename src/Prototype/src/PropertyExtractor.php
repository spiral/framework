<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    private Parser $parser;

    /**
     * @param Parser|null $parser
     */
    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?? (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    /**
     * Get list of all virtual property names.
     *
     * @return string[]
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
