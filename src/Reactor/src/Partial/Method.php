<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Spiral\Reactor\AbstractDeclaration;
use Spiral\Reactor\Aggregator\Parameters;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\ReplaceableInterface;
use Spiral\Reactor\Traits\AccessTrait;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\NamedTrait;

/**
 * Represent class method.
 */
class Method extends AbstractDeclaration implements ReplaceableInterface, NamedInterface
{
    use NamedTrait;
    use CommentTrait;
    use AccessTrait;

    private bool $static = false;
    private ?string $return = null;
    private Parameters $parameters;
    private ?Source $source = null;

    public function __construct(string $name, string|array $source = '', string|array $comment = '')
    {
        $this->setName($name);
        $this->parameters = new Parameters([]);
        $this->initSource($source);
        $this->initComment($comment);
    }

    public function setStatic(bool $static = true): Method
    {
        $this->static = $static;

        return $this;
    }

    public function setReturn(string $return): Method
    {
        $this->return = $return;

        return $this;
    }

    public function isStatic(): bool
    {
        return $this->static;
    }

    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * Set method source.
     */
    public function setSource(array|string $source): Method
    {
        if (!empty($source)) {
            if (\is_array($source)) {
                $this->source->setLines($source);
            } elseif (\is_string($source)) {
                $this->source->setString($source);
            }
        }

        return $this;
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    public function parameter(string $name): Parameter
    {
        return $this->parameters->get($name);
    }

    public function replace(string|array $search, string|array $replace): Method
    {
        $this->docComment->replace($search, $replace);

        return $this;
    }

    public function render(int $indentLevel = 0): string
    {
        $result = '';
        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        $method = $this->renderModifiers();
        if (!$this->parameters->isEmpty()) {
            $method .= "({$this->parameters->render()})";
        } else {
            $method .= '()';
        }

        if ($this->return) {
            $method .= ": {$this->return}";
        }

        $result .= $this->addIndent($method, $indentLevel) . "\n";
        $result .= $this->addIndent('{', $indentLevel) . "\n";

        if (!$this->source->isEmpty()) {
            $result .= $this->source->render($indentLevel + 1) . "\n";
        }

        return $result . $this->addIndent('}', $indentLevel);
    }

    private function renderModifiers(): string
    {
        $chunks = [$this->getAccess()];

        if ($this->isStatic()) {
            $chunks[] = 'static';
        }

        $chunks[] = "function {$this->getName()}";

        return \implode(' ', $chunks);
    }

    /**
     * Init source value.
     */
    private function initSource(array|string $source): void
    {
        $this->source ??= new Source();

        if (!empty($source)) {
            if (\is_array($source)) {
                $this->source->setLines($source);
            } elseif (\is_string($source)) {
                $this->source->setString($source);
            }
        }
    }
}
