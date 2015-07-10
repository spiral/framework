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
 * Behaviours created by node supervisor to explain html parser how to treat some template specific
 * constructions, such as block, includes or extends command.
 */
interface BehaviourInterface
{
    /**
     * Simple behaviour constants.
     */
    const SKIP_TOKEN = false;
    const SIMPLE_TAG = true;
}