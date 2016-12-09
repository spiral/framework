<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Traits\SharedTrait;

/**
 * Generic spiral service only provide simplified access to shared components and instances. Every
 * service is singleton.
 */
class Service extends Component implements SingletonInterface
{
    use SharedTrait;
}