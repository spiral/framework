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
use Spiral\Components\View\Compiler\Processors\Templater\NodeSupervisorInterface;
use Spiral\Support\Html\Tokenizer;

class IncludeBehaviour implements BehaviourInterface
{
    protected static $index = 0;

    protected $supervisor = null;

    protected $attributes = [];

    protected $context = [];

    protected $name = '';

    public function __construct(NodeSupervisorInterface $supervisor, $name, array $context, array $attributes = [])
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

    public function getNode()
    {
        $included = new Node($this->supervisor, $this->getUniqueID());

        //Change that
        $included->handleBehaviour(
            new ExtendBehaviour($this->supervisor->getNode($this->name, $this->name), [])
        );

        $included->registerBlock('context', [], [$this->getContext()]);

        foreach ($this->getAttributes() as $attribute => $value)
        {
            //We should replace attribute value
            $included->registerBlock($attribute, [], [$value]);
        }

        //TODO: Create mixed supervisor here OR add something
        //TODO: or something else?
        //TODO: we can do custom supervisor inside specific block

        //dump($this->supervisor->uses);

        $compiled = [];
        $outerBlocks = [];
        $compiled = $included->compile($compiled, $outerBlocks);

        $compiled = $this->supervisor->mountOuterBlocks($compiled, $outerBlocks);

        return new Node($this->supervisor, $this->getUniqueID(), $compiled);
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