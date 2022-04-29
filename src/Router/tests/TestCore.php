<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Core\CoreInterface;

class TestCore implements CoreInterface
{
    private $core;

    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    public function callAction(string $controller, string $action = null, array $parameters = []): string
    {
        return '@wrapped.' . $this->core->callAction($controller, $action, $parameters);
    }
}
