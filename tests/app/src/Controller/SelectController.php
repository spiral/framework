<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\App\Controller;

use Cycle\ORM\Select;

class SelectController
{
    public function select(Select $users)
    {
        return $users->count();
    }
}
