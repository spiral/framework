<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassDeclaration;

use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\Traits\AccessTrait;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\SerializerTrait;

/**
 * Declares property element.
 */
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
     * @param string $name
     * @param null   $defaultValue
     * @param string $comment
     */
    public function __construct($name, $defaultValue = null, $comment = '')
    {
        parent::__construct($name);
        $this->setDefault($defaultValue);
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
     * @return mixed
     */
    public function getDefault()
    {
        return $this->defaultValue;
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

        $result .= $this->indent("{$this->access} \${$this->getName()}", $indentLevel);

        if ($this->hasDefault) {
            $value = $this->serializer()->serialize($this->defaultValue);

            if (is_array($this->defaultValue)) {
                $value = $this->mountIndents($value, $indentLevel);
            }

            $result .= " = {$value};";
        } else {
            $result .= ";";
        }

        return $result;
    }

    /**
     * Mount indentation to value.
     *
     * @param $serialized
     * @param $indentLevel
     * @return string
     */
    private function mountIndents($serialized, $indentLevel)
    {
        $lines = explode("\n", $serialized);
        foreach ($lines as &$line) {
            $line = $this->indent($line, $indentLevel);
            unset($line);
        }

        return ltrim(join("\n", $lines));
    }
}