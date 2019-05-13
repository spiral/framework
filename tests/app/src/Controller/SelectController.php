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
use Spiral\Core\Controller;

class SelectController extends Controller
{
    public function selectAction(Select $users)
    {
        return $users->count();
    }
}