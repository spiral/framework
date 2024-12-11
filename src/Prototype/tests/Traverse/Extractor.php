<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Traverse;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class Extractor
{
    /** @var Parser */
    private $parser;

    public function __construct(?Parser $parser = null)
    {
        $this->parser = $parser ?? (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    public function extractFromFilename(string $filename): array
    {
        return $this->extractFromString(file_get_contents($filename));
    }

    public function extractFromString(string $code): array
    {
        $params = new ConstructorParamsVisitor();
        $this->traverse($code, $params);

        return $params->getParams();
    }

    private function traverse(string $code, NodeVisitor ...$visitors): void
    {
        $tr = new NodeTraverser();
        foreach ($visitors as $visitor) {
            $tr->addVisitor($visitor);
        }

        $tr->traverse($this->parser->parse($code));
    }
}
