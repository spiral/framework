<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Controllers;

use Spiral\Core\Controller;
use Spiral\Core\Traits\AuthorizesTrait;

class AuthorizesController extends Controller
{
    use AuthorizesTrait;

    public function allowsAction()
    {
        return $this->allows('do');
    }

    public function authorizesAction()
    {
        $this->authorize('do');

        return true;
    }
}