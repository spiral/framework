<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);


namespace Spiral\App\Controller;

use Spiral\Controller\Traits\AuthorizesTrait;
use Spiral\Core\Controller;

class AuthController extends Controller
{
    use AuthorizesTrait;

    public function doAction()
    {
        $this->authorize('do');
        return 'ok';
    }
}
