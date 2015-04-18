<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Router;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Components\Http\Request;
use Spiral\Core\CoreInterface;
use Spiral\Core\Dispatcher\ClientException;

class ResourceRoute extends Route
{
    /**
     * Create route to map to controller methods based on HTTP method, default url patterns is:
     *
     * GET     /resource      => Controller->index()
     * PUT     /resource      => Controller->create()
     * POST    /resource      => Controller->create()
     * GET     /resource/id   => Controller->retrieve(id)
     * PUT     /resource/id   => Controller->update(id)
     * POST    /resource/id   => Controller->update(id)
     * DELETE  /resource/id   => Controller->delete(id)
     *
     * @param string $resource   Resource name.
     * @param string $controller Controller class.
     * @return ResourceRoute
     */
    public function __construct($resource, $controller)
    {
        parent::__construct('resource::' . $resource, $resource . '(/<id>)', $controller . '::');
    }

    /**
     * Execute controller action chain resolved based on provided string target. ResourceRoute will
     * use request method to resolve action.
     *
     * @param ServerRequestInterface $request
     * @param CoreInterface          $core
     * @return mixed
     */
    protected function callAction(ServerRequestInterface $request, CoreInterface $core)
    {
        $controller = rtrim($this->target, '::');

        $method = strtoupper($request->getMethod());

        $action = null;
        switch ($method)
        {
            case 'GET':
                $action = !empty($this->matches['id']) ? 'retrieve' : 'index';
                break;
            case 'PUT':
            case 'POST':
                $action = !empty($this->matches['id']) ? 'update' : 'create';
                break;

            case 'DELETE':
                if (!empty($this->matches['id']))
                {
                    $action = 'delete';
                }
                break;
        }

        if (empty($action))
        {
            throw new ClientException();
        }

        return $core->callAction($controller, $action, $this->matches);
    }
}