<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Behaviours;

use Spiral\Stempler\BehaviourInterface;

/**
 * Defines new block.
 */
class BlockBehaviour implements BehaviourInterface
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Created block name.
     *
     * @return string
     */
    public function blockName()
    {
        return $this->name;
    }
}