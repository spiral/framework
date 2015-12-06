<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassDeclaration;

use Spiral\Reactor\Body\Source;
use Spiral\Reactor\ClassDeclaration\Aggregators\ParameterAggregator;
use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\ReplaceableInterface;
use Spiral\Reactor\Traits\AccessTrait;
use Spiral\Reactor\Traits\CommentTrait;

/**
 * Represent class method.
 */
class MethodDeclaration extends NamedDeclaration implements ReplaceableInterface
{
    use CommentTrait, AccessTrait;

    /**
     * @var bool
     */
    private $static = false;

    /**
     * @var ParameterAggregator
     */
    private $parameters = null;

    /**
     * @var Source
     */
    private $source = null;

    /**
     * @param string $name
     * @param string $source
     * @param string $comment
     */
    public function __construct($name, $source = '', $comment = '')
    {
        parent::__construct($name);

        $this->parameters = new ParameterAggregator([]);

        $this->initSource($source);
        $this->initComment($comment);
    }

    /**
     * @param bool $static
     * @return $this
     */
    public function setStatic($static)
    {
        $this->static = (bool)$static;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * @return Source
     */
    public function source()
    {
        return $this->source;
    }

    /**
     * Set method source.
     *
     * @param string|array $source
     * @return $this
     */
    public function setSource($source)
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
     * @return ParameterAggregator|ParameterDeclaration[]
     */
    public function parameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     * @return ParameterDeclaration
     */
    public function parameter($name)
    {
        return $this->parameters->get($name);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function replace($search, $replace)
    {
        $this->docComment->replace($search, $replace);

        return $this;
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function render($indentLevel = 0)
    {
        $result = '';
        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        $method = "{$this->getAccess()} function {$this->getName()}";
        if (!$this->parameters->isEmpty()) {
            $method .= "({$this->parameters->render()})";
        } else {
            $method .= "()";
        }

        $result .= $this->indent($method, $indentLevel) . "\n";
        $result .= $this->indent('{', $indentLevel) . "\n";

        if (!$this->source->isEmpty()) {
            $result .= $this->source->render($indentLevel + 1) . "\n";
        }

        $result .= $this->indent("}", $indentLevel);

        return $result;
    }

    /**
     * Init source value.
     *
     * @param string $source
     */
    private function initSource($source)
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