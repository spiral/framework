<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\ClassElements;

use Spiral\Reactor\AbstractElement;
use Spiral\Reactor\ClassElements\MethodElements\ParameterElement;

/**
 * Represent class method.
 */
class MethodElement extends AbstractElement
{
    /**
     * @var string
     */
    private $access = self::ACCESS_PUBLIC;

    /**
     * @var bool
     */
    private $static = false;

    /**
     * @var ParameterElement[]
     */
    private $parameters = [];

    /**
     * Method source in form of code lines.
     *
     * @var array
     */
    private $source = [];

    /**
     * @param string $access
     * @return $this
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
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
     * Get existed method parameter by it's name or create new one. Parameter type will be used to
     * generate method DocComment.
     *
     * @param string $name
     * @param string $type
     * @return ParameterElement
     */
    public function parameter($name, $type = '')
    {
        if (!isset($this->parameters[$name])) {
            $this->parameters[$name] = new ParameterElement($name);
        }

        if (
            !empty($type)
            && !in_array($docComment = "@param {$type} \${$name}", $this->comment)
        ) {
            $this->comment[] = $docComment;
        }

        return $this->parameters[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * @param string $name
     */
    public function removeParameter($name)
    {
        unset($this->parameters[$name]);
    }

    /**
     * @return ParameterElement[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Replace method source with new value.
     *
     * @param string|array $source
     * @param bool         $append
     * @return $this
     */
    public function setSource($source, $append = false)
    {
        if (is_array($source)) {
            if ($append) {
                $this->source = array_merge($this->source, $source);
            } else {
                $this->source = $source;
            }

            return $this;
        }

        //Normalizing endings
        $lines = explode("\n", preg_replace('/[\n\r]+/', "\n", $source));
        $indentLevel = 0;

        if (!$append) {
            $this->source = [];
        }

        foreach ($lines as $line) {
            //Cutting start spaces
            $line = trim($line);

            if (strpos($line, '}') !== false) {
                $indentLevel--;
            }

            $this->source[] = $this->indent($line, $indentLevel);
            if (strpos($line, '{') !== false) {
                $indentLevel++;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function replaceComments($search, $replace)
    {
        parent::replaceComments($search, $replace);

        foreach ($this->parameters as $parameter) {
            $parameter->setType(
                str_replace($search, $replace, $parameter->getType())
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function lines($indent = 0)
    {
        $lines = $this->commentLines();

        //Parameters
        $parameters = [];
        foreach ($this->parameters as $parameter) {
            $parameters[] = $parameter->render();
        }

        $declaration = $this->access . ' ' . ($this->static ? 'static ' : '');
        $declaration .= 'function ' . $this->getName() . '(' . join(', ', $parameters) . ')';

        $lines[] = $declaration;

        $lines[] = '{';
        $lines = array_merge($lines, $this->indentLines($this->source, 1));
        $lines[] = '}';

        return $this->indentLines($lines, $indent);
    }

    /**
     * Clone method parameters and comments using ReflectionMethod.
     *
     * @param \ReflectionMethod $method
     */
    public function cloneSchema(\ReflectionMethod $method)
    {
        $this->setComment($method->getDocComment());

        $this->static = $method->isStatic();
        if ($method->isPrivate()) {
            $this->setAccess(self::ACCESS_PRIVATE);
        } elseif ($method->isProtected()) {
            $this->setAccess(self::ACCESS_PROTECTED);
        }

        foreach ($method->getParameters() as $reflection) {
            $parameter = $this->parameter($reflection->getName());
            if ($reflection->isOptional()) {
                $parameter->setOptional(true, $reflection->getDefaultValue());
            }

            $reflection->isArray() && $parameter->setType('array');
            if (!empty($reflection->getClass())) {
                $parameter->setType('\\' . ltrim($reflection->getClass()->getName(), '\\'));
            }

            $parameter->setPBR($reflection->isPassedByReference());
        }
    }
}