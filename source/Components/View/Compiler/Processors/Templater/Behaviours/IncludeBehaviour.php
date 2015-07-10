<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Behaviours;

use Spiral\Components\View\Compiler\Processors\Templater\BehaviourInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Node;
use Spiral\Components\View\Compiler\Processors\Templater\NodeSupervisor;
use Spiral\Support\Html\Tokenizer;

class IncludeBehaviour implements BehaviourInterface
{
    protected static $index = 0;

    protected $supervisor = null;

    protected $attributes = [];

    protected $context = [];

    protected $name = '';

    public function __construct(NodeSupervisor $supervisor, $name, array $context, array $attributes = [])
    {
        $this->supervisor = $supervisor;
        $this->context = $context;
        $this->attributes = $attributes;
        $this->name = $name;
    }

    protected function getUniqueID()
    {
        return md5(self::$index++);
    }

    protected function getIncludedContent()
    {
        $included = new Node($this->supervisor, $this->getUniqueID());
    }

    public function getNode()
    {
        $included = new Node($this->supervisor, $this->getUniqueID());

        //        //Change that
        $included->handleBehaviour(
            new ExtendBehaviour(
                $this->supervisor->getNode($this->name, $this->name),
                []
            )
        );

        $included->registerBlock('context', [], [$this->context]);

        foreach ($this->getAttributes() as $attribute => $value)
        {
            $included->registerBlock($attribute, [], [$value]);
        }

        //TODO: Create mixed supervisor here OR add something
        //TODO: or something else?
        //TODO: we can do custom supervisor inside specific block
        return new Node($this->supervisor, $this->getUniqueID(), $included->compile());
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        $context = '';

        foreach ($this->context as $token)
        {
            $context .= $token[Tokenizer::TOKEN_CONTENT];
        }

        return $context;
    }

    public function __debugInfo()
    {
        return (object)[
            'attributes' => $this->attributes,
            'context'    => $this->getContext()
        ];
    }
}