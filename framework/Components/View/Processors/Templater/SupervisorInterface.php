<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors\Templater;

interface SupervisorInterface
{
    /**
     * Get token behaviour. Return null or empty if token has to be removed from rendering.
     *
     * Behaviours separated by 3 types:
     * import  - following token is importing another node
     * block   - token describes node block which can be extended
     * extends - request to extend current node from known parent node.
     *
     * To keep token without defining behaviour - return true.
     *
     * @param array $token
     * @param Node  $node Currently active node.
     * @return mixed|Behaviour
     */
    public function describeToken(&$token, Node $node = null);

    /**
     * Regular expression for detecting short tags. Short tags will not be processed if result of this function is empty.
     *
     * @return mixed
     */
    public function getShortExpression();
}