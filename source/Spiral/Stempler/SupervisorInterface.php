<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler;

/**
 * SupervisorInterface used by Node to define html syntax for control elements and create valid
 * behaviour for html constructions.
 *
 * @see BehaviourInterface
 * @see ExtendsBehaviourInterface
 * @see BlockBehaviourInterface
 * @see IncludeBehaviourInterface
 */
interface SupervisorInterface
{
    /**
     * @return SyntaxInterface
     */
    public function syntax();

    /**
     * Define html tag behaviour based on supervisor syntax settings.
     *
     * @param array $token
     * @param array $content
     * @param Node  $node Node which called behaviour creation. Just in case.
     * @return mixed|BehaviourInterface
     */
    public function tokenBehaviour(array $token, array $content, Node $node);
}