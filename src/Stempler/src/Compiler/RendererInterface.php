<?php

declare(strict_types=1);

namespace Spiral\Stempler\Compiler;

use Spiral\Stempler\Compiler;
use Spiral\Stempler\Node\NodeInterface;

interface RendererInterface
{
    public function render(Compiler $compiler, Result $result, NodeInterface $node): bool;
}
