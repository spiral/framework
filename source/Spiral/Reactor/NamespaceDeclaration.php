<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Prototypes\Declaration;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\ElementsTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Represent namespace declaration. Attention, namespace renders in a form of namespace name { ... }
 */
class NamespaceDeclaration extends Declaration
{
    use UsesTrait, CommentTrait, ElementsTrait;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @param string $name
     */
    public function __construct($name = '')
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        $result = '';
        $indentShift = 0;

        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        if (!empty($this->name)) {
            $result = $this->indent("namespace {$this->name} {", $indentLevel);
            $indentShift = 1;
        }

        if (!empty($this->uses)) {
            $result .= $this->renderUses($indentLevel + $indentShift) . "\n";
        }

        foreach ($this->elements as $element) {
            $result .= $element->render($indentLevel + $indentShift);
        }

        if (!empty($this->name)) {
            $result = $this->indent("}", $indentLevel);
        }

        return $result;
    }
}