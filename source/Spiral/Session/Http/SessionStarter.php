<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Http\Cookies\Cookie;
use Spiral\Http\MiddlewareInterface;
use Spiral\Session\SessionStore;

/**
 * HttpMiddleware used to create and commit session data using cookies as sessionID provider.
 * Expected to work with SessionStore class.
 */
class SessionStarter implements MiddlewareInterface
{
    /**
     * Cookie to store session ID in.
     */
    const COOKIE = 'session';

    /***
     * @var SessionStore
     */
    private $store = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param SessionStore $store
     */
    public function setStore(SessionStore $store)
    {
        $this->store = $store;
    }

    /**
     * Get associated store instance of fetch one from container.
     *
     * @return SessionStore
     */
    protected function store()
    {
        if (!empty($this->store)) {
            return $this->store;
        }

        return $this->store = $this->container->get(SessionStore::class);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $cookies = $request->getCookieParams();

        $outerID = null;
        if (isset($cookies[self::COOKIE])) {
            if ($this->store()->isStarted()) {
                $outerID = $this->store()->getID();
            }

            //Mounting ID retrieved from cookies
            $this->store()->setID($cookies[self::COOKIE]);
        }

        $response = $next();

        if (empty($this->store) && $this->container->hasInstance(SessionStore::class)) {
            //Store were started by itself
            $this->store = $this->container->get(SessionStore::class);
        }

        if (!empty($this->store) && ($this->store->isStarted() || $this->store->isDestroyed())) {
            $response = $this->setCookie($request, $response, $this->store, $cookies);
        }

        //Restoring original session, not super efficient operation
        if (!empty($outerID)) {
            $this->store->setID($outerID);
        }

        return $response;
    }

    /**
     * Mount session id or remove session cookie.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param SessionStore           $store
     * @param array                  $cookies
     * @return ResponseInterface
     */
    protected function setCookie(
        ServerRequestInterface $request,
        ResponseInterface $response,
        SessionStore $store,
        array $cookies
    ) {
        if ($store->isStarted()) {
            $store->commit();
        }

        if (!isset($cookies[self::COOKIE]) || $cookies[self::COOKIE] != $store->getID()) {
            if ($response instanceof ResponseInterface) {
                return $response->withAddedHeader(
                    'Set-Cookie',
                    Cookie::create(
                        self::COOKIE,
                        $store->getID(),
                        $store->config()['lifetime'],
                        $request->getAttribute('basePath'),
                        $request->getAttribute('cookieDomain')
                    )->packHeader()
                );
            }
        }

        return $response;
    }
}