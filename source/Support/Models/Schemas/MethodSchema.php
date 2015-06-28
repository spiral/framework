<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models\Schemas;

use Spiral\Support\Generators\Reactor\ClassElements\MethodElement;

class MethodSchema extends MethodElement
{
    /**
     * Method return value.
     *
     * @var string
     */
    protected $returnValue = 'void';

    /**
     * New MethodSchema instance.
     *
     * @param \ReflectionMethod $reflection
     */
    public function __construct(\ReflectionMethod $reflection)
    {
        $this->cloneSchema($reflection);
        $this->name = $reflection->getName();

        //Looking for return value
        foreach ($this->docComment as $line)
        {
            if (preg_match('/\@return\s*([^\n ]+)/i', $line, $matches))
            {
                $this->returnValue = trim($matches[1]);
            }
        }
    }

    /**
     * Method return value.
     *
     * @return string
     */
    public function getReturn()
    {
        return $this->returnValue;
    }
}