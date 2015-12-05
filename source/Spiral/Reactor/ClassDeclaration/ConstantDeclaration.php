<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassElements;

use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\SerializerTrait;

class ConstantDeclaration extends NamedDeclaration
{
    use CommentTrait, SerializerTrait;

    /**
     * Constants and properties.
     */
    use CommentTrait, SerializerTrait;

    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @param string       $name
     * @param string       $value
     * @param string|array $comment
     */
    public function __construct($name, $value, $comment = '')
    {
        parent::__construct($name);
        $this->value = $value;
        $this->initComment($comment);
    }

    /**
     * Array values allowed (but works in PHP7 only).
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        $result = '';
        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        //todo: make indent level work
        return "{$result}const {$this->getName()} = " . $this->serializer()->serialize(
            $this->value,
            $indentLevel
        );
    }
}