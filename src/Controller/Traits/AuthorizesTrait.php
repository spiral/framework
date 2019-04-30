<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Controller\Traits;

use Spiral\Core\Exception\ControllerException;
use Spiral\Security\Traits\GuardedTrait;

/**
 * Authorizes method and throws an exception in case of failure.
 */
trait AuthorizesTrait
{
    use GuardedTrait;

    /**
     * Authorize permission or thrown controller exception.
     *
     * @param string $permission
     * @param array  $context
     * @return bool
     *
     * @throws ControllerException
     */
    protected function authorize(string $permission, array $context = []): bool
    {
        if (!$this->allows($permission, $context)) {
            $name = $this->resolvePermission($permission);

            throw new ControllerException(
                "Unauthorized permission '{$name}'",
                ControllerException::FORBIDDEN
            );
        }

        return true;
    }

    /**
     * Ensuring that trait can only be associated with controllers.
     *
     * @param string|null $action
     * @param array       $parameters
     * @return mixed
     */
    abstract public function callAction(string $action = null, array $parameters = []);
}