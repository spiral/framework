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
    public $attributes = array();

    /**
     * Additional node options, such as node should force it's own content, be called from parent namespace and etc.
     *
     * @var array
     */
    public $options = array();

    /**
     * Context node, parent in case of extending or importing.
     *
     * @var Node
     */
    public $contextNode = null;

    /**
     * Create new smart token. Object will explain to node tokenizer what this token is, it can either be import, extend
     * request or block definition.
     *
     * @param string $name       Block name, not required for extend or import types.
     * @param string $type       Token behaviour type (import, extend, block)
     * @param array  $attributes Array or token attributes, valid array from html\Tokenizer.
     * @param array  $options    Additional node options.
     */
    public function __construct($name, $type, array $attributes = array(), array $options = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->attributes = $attributes;
        $this->options = $options;
    }
}