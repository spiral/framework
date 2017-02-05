<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Core;

use Spiral\Core\Traits\SharedTrait;

/**
 * Generic spiral service only provide simplified access to shared components and instances.
 */
class Service extends Component
{
    use SharedTrait;
}