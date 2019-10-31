<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Domain;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

class GuardInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        // TODO: Implement process() method.
    }
}