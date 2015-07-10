<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

interface SupervisorInterface
{
    public function getBehaviour(array $token, array $content, Node $node);

    public function mountOuterBlocks($content, array $blocks);
}