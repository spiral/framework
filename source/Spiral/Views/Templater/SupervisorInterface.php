<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

/**
 * SupervisorInterface used by Node to define html syntax for control elements and create valid
 * behaviour for html constructions.
 */
interface SupervisorInterface
{
    /**
     * Define html tag behaviour based on supervisor syntax settings.
     *
     * @param array $token
     * @param array $content
     * @param Node  $node
     * @return mixed|BehaviourInterface
     */
    public function getBehaviour(array $token, array $content, Node $node);
}