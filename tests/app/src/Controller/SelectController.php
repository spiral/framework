<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\App\Controller;

use Spiral\Tests\App\User\UserRepository;

class SelectController
{
    public function select(UserRepository $users)
    {
        return $users->select()->count();
    }
}
