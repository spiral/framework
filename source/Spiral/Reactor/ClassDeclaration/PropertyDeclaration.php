<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassElements;

use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\Traits\AccessTrait;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\SerializerTrait;

class PropertyDeclaration extends NamedDeclaration
{
    use CommentTrait, SerializerTrait, AccessTrait;

    /**
     * @var bool
     */
    private $hasDefault = false;

    /**
     * @var mixed
     */
    private $defaultValue = null;

    /**
     * @param string       $name
     * @param string|array $comment
     */
    public function __construct($name, $comment = '')
    {
        parent::__construct($name);
        $this->initComment($comment);
    }

    /**
     * Has default value.
     *
     * @return bool
     */
    public function hasDefault()
    {
        return $this->hasDefault;
    }

    /**
     * Set default value.
     *
     * @param mixed $value
     * @return $this
     */
    public function setDefault($value)
    {
        $this->hasDefault = true;
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Remove default value.
     *
     * @return $this
     */
    public function removeDefault()
    {
        $this->hasDefault = false;
        $this->defaultValue = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        $result = '';
        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel);
        }

        $result .= "{$this->access} \${$this->getName()}";

        if ($this->hasDefault) {
            //todo: make indent level work
            $result .= $this->serializer->serialize($this->defaultValue, $indentLevel);
        } else {
            $result .= ";";
        }

        return $result;
    }

    /**
     * Returns aggregator for property name elements.
     *
     * @param string $name
     * @return mixed
     * @throws ReactorException
     */
    public function __get($name)
    {
        if ($name == 'comment') {
            return $this->comment();
        }

        throw new ReactorException("Undefined property '{$name}'.");
    }
}