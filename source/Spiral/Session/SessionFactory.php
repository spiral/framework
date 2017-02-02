<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session;

use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Session\Configs\SessionConfig;
use Spiral\Session\Exceptions\MultipleSessionException;
use Spiral\Session\Exceptions\SessionException;

/**
 * Initiates session instance and configures session handlers.
 */
class SessionFactory extends Component implements SingletonInterface
{
    use BenchmarkTrait;

    /**
     * @var \Spiral\Session\Configs\SessionConfig
     */
    private $config;

    /**
     * @var \Spiral\Core\FactoryInterface
     */
    private $factory;

    /**
     * Currently initiated session instance.
     *
     * @var SessionInterface
     */
    private $session = null;

    /**
     * @param \Spiral\Session\Configs\SessionConfig $config
     * @param \Spiral\Core\FactoryInterface         $factory
     */
    public function __construct(SessionConfig $config, FactoryInterface $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * @return \Spiral\Session\SessionInterface
     *
     * @throws \Spiral\Session\Exceptions\MultipleSessionException
     */
    public function initSession(): SessionInterface
    {
        if (!empty($this->session)) {
            throw new MultipleSessionException("Unable to initiate session, session already initiated");
        }

        //No automatic cookies
        ini_set('session.use_cookies', false);

        //Initiating proper session handler
        if ($this->config->sessionHandler() !== null) {
            try {
                $handler = $this->initHandler($this->config->sessionHandler());
            } catch (\Throwable $e) {
                throw new SessionException(
                    "Unable to init session handler {$this->config->sessionHandler()}",
                    $e->getCode(),
                    $e
                );
            }

            session_set_save_handler($handler, true);
        }

        return $this->session = $this->factory->make(SessionInterface::class);
    }

    /**
     * @param string $handler
     *
     * @return mixed|null|object
     */
    protected function initHandler(string $handler)
    {
        $benchmark = $this->benchmark('handler', $handler);
        try {
            //Init handler
            return $this->factory->make(
                $this->config->handlerClass($handler),
                $this->config->handlerOptions($handler)
            );
        } finally {
            $this->benchmark($benchmark);
        }
    }
}