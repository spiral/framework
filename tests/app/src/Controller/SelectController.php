<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\App\User\UserRepository;

class SelectController
{
    public function select(UserRepository $users)
    {
        return $users->select()->count();
    }
}
