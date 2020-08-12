<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\App\Controller;

use Spiral\Tests\App\User\Role;
use Spiral\Tests\App\User\User;
use Spiral\Domain\Annotation\Guarded;

class DemoController
{
    public function entity(User $user)
    {
        return $user->getName();
    }

    public function entity2(User $user, Role $role)
    {
        return 'ok';
    }

    /**
     * @Guarded()
     * @return string
     */
    public function guardedButNoName()
    {
        return 'ok';
    }

    /**
     * @Guarded("do")
     * @return string
     */
    public function do()
    {
        return 'ok';
    }
}
