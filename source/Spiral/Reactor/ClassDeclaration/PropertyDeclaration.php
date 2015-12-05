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

class PropertyDeclaration extends NamedDeclaration
{
    use CommentTrait, SerializerTrait;

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
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        // TODO: Implement render() method.
    }
}