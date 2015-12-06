<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassDeclaration;

use Spiral\Reactor\Body\DocComment;
use Spiral\Reactor\Body\Source;
use Spiral\Reactor\ClassElements\MethodElements\ParameterElement;
use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\ReplaceableInterface;
use Spiral\Reactor\Traits\AccessTrait;
use Spiral\Reactor\Traits\CommentTrait;

/**
 * Represent class method.
 *
 * @property-read DocComment $comment
 */
class MethodDeclaration extends NamedDeclaration implements ReplaceableInterface
{
    use CommentTrait, AccessTrait;

    /**
     * @var bool
     */
    private $static = false;

    /**
     * @var ParameterElement[]
     */
    private $parameters = [];

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

//    /**
//     * Get existed method parameter by it's name or create new one. Parameter type will be used to
//     * generate method DocComment.
//     *
//     * @param string $name
//     * @param string $type
//     * @return ParameterElement
//     */
//    public function parameter($name, $type = '')
//    {
//        if (!isset($this->parameters[$name])) {
//            $this->parameters[$name] = new ParameterElement($name);
//        }
//
//        if (
//            !empty($type)
//            && !in_array($docComment = "@param {$type} \${$name}", $this->comment)
//        ) {
//            $this->comment[] = $docComment;
//        }
//
//        return $this->parameters[$name];
//    }
//
//    /**
//     * @param string $name
//     * @return bool
//     */
//    public function hasParameter($name)
//    {
//        return array_key_exists($name, $this->parameters);
//    }
//
//    /**
//     * @param string $name
//     */
//    public function removeParameter($name)
//    {
//        unset($this->parameters[$name]);
//    }
//
//    /**
//     * @return ParameterElement[]
//     */
//    public function getParameters()
//    {
//        return $this->parameters;
//    }

//    /**
//     * Replace method source with new value.
//     *
//     * @param string|array $source
//     * @param bool         $append
//     * @return $this
//     */
//    public function setSource($source, $append = false)
//    {
//        if (is_array($source)) {
//            if ($append) {
//                $this->source = array_merge($this->source, $source);
//            } else {
//                $this->source = $source;
//            }
//
//            return $this;
//        }
//
//        //Normalizing endings
//        $lines = explode("\n", preg_replace('/[\n\r]+/', "\n", $source));
//        $indentLevel = 0;
//
//        if (!$append) {
//            $this->source = [];
//        }
//
//        foreach ($lines as $line) {
//            //Cutting start spaces
//            $line = trim($line);
//
//            if (strpos($line, '}') !== false) {
//                $indentLevel--;
//            }
//
//            $this->source[] = $this->indent($line, $indentLevel);
//            if (strpos($line, '{') !== false) {
//                $indentLevel++;
//            }
//        }
//
//        return $this;
//    }
//
//    /**
//     * @return array
//     */
//    public function getSource()
//    {
//        return $this->source;
//    }

//    /**
//     * {@inheritdoc}
//     */
//    public function replaceComments($search, $replace)
//    {
//        parent::replaceComments($search, $replace);
//
//        foreach ($this->parameters as $parameter) {
//            $parameter->setType(
//                str_replace($search, $replace, $parameter->getType())
//            );
//        }
//
//        return $this;
//    }

    /**
     * @return Source
     */
    public function source()
    {
        return $this->source;
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
     * @todo DRY
     * @param string $name
     * @return mixed
     * @throws ReactorException
     */
    public function __get($name)
    {
        switch ($name) {
            case 'source':
                return $this->source();
            case 'comment':
                return $this->comment();
        }

        throw new ReactorException("Undefined property '{$name}'.");
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function render($indentLevel = 0)
    {

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