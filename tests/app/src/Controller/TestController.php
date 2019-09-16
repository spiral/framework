<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\App\Request\BadRequest;
use Spiral\App\Request\TestRequest;
use Spiral\Core\Controller;
use Spiral\Filter\RequestInput;
use Spiral\Http\PaginationFactory;
use Spiral\Pagination\Paginator;
use Spiral\Router\RouteInterface;
use Spiral\Translator\Traits\TranslatorTrait;

class TestController extends Controller
{
    use TranslatorTrait;

    public function indexAction(string $name = 'Dave')
    {
        return "Hello, {$name}.";
    }

    public function paginateAction(PaginationFactory $paginationFactory)
    {
        /** @var Paginator $p */
        $p = $paginationFactory->createPaginator('page');

        return $p->withCount(1000)->getPage();
    }

    public function filterAction(TestRequest $r)
    {
        return $r->isValid() ? ($r->value ?? 'ok') : json_encode($r->getErrors());
    }

    public function filter2Action(BadRequest $r)
    {
    }

    public function inputAction(RequestInput $i)
    {
        return 'value: ' . $i->withPrefix('section')->getValue('query', 'value');
    }

    public function errorAction()
    {
        echo $undefined;
    }

    public function routeAction(RouteInterface $route)
    {
        return $route->getMatches();
    }

    public function payloadAction(ServerRequestInterface $request)
    {
        return $request->getParsedBody();
    }

    public function requiredAction(int $id)
    {
        //no index
        $this->say(get_class($this));
        $this->say('Hello world');
        $this->say('Hello world', [], 'external');

        l('l');
        l('l', [], 'external');
        p('%s unit|%s units', 10);
        p('%s unit|%s units', 10, [], 'external');

        return $id;
    }
}
