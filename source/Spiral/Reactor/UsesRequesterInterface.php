<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

/**
 * Declares needed uses and aliases in array form.
 */
interface UsesRequesterInterface
{
    /**
     * Must return needed uses in array form [class => alias|null] to be automatically merged
     * with existed import set.
     *
     * @return array
     */
    public function requestsUses();
}