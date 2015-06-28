<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors\Templater;

class Behaviour
{
    /**
     * Public node name.
     *
     * @var string
     */
    public $name = '';

    /**
     * Token behaviour. Specified how Node should processing this token: extend call, block or import.
     *
     * @var null|int
     */
    public $type = null;

    /**
     * HTML Token (tag) attributes.
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Additional node options, such as node should force it's own content, be called from parent
     * namespace and etc.
     *
     * @var array
     */
    public $options = [];

    /**
     * Context node, parent in case of extending or importing.
     *
     * @var Node
     */
    public $contextNode = null;

    /**
     * Behaviours created by supervisor instance and explains to Node how to process tag.
     *
     * @param string $name       Block name, not required for extend or import types.
     * @param string $type       Token behaviour type (import, extend, block)
     * @param array  $attributes Array or token attributes, valid array from Html\Tokenizer.
     * @param array  $options    Additional node options, such options will be passed to every node
     *                           child.
     */
    public function __construct($name, $type, array $attributes = [], array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->attributes = $attributes;
        $this->options = $options;
    }
}