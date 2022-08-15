<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /** @var bool */
    private $static = false;

    /** @var string */
    private $return;

    /** @var Parameters */
    private $parameters;

    /** @var Source */
    private $source;

    /**
     * @param string|array $source
     * @param string|array $comment
     */
    public function __construct(string $name, $source = '', $comment = '')
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

    /**
     * Rename to getSource()?
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * Set method source.
     *
     * @param string|array $source
     */
    public function setSource($source): Method
    {
        if (!empty($source)) {
            if (is_array($source)) {
                $this->source->setLines($source);
            } elseif (is_string($source)) {
                $this->source->setString($source);
            }
        }

        return $this;
    }

    /**
     * @return Parameters|Parameter[]
     */
    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    public function parameter(string $name): Parameter
    {
        return $this->parameters->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function replace($search, $replace): Method
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

        return implode(' ', $chunks);
    }

    /**
     * Init source value.
     *
     * @param string|array $source
     */
    private function initSource($source): void
    {
        if (empty($this->source)) {
            $this->source = new Source();
        }

        if (!empty($source)) {
            if (is_array($source)) {
                $this->source->setLines($source);
            } elseif (is_string($source)) {
                $this->source->setString($source);
            }
        }
    }
}
