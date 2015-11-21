<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler;

/**
 * Behaviours created by node supervisor to explain html Nodes how to treat some template specific
 * constructions, such as block, include or extends commands.
 */
interface BehaviourInterface
{
    /**
     * Simple behaviour constants.
     */
    const SKIP_TOKEN = false;
    const SIMPLE_TAG = true;
}