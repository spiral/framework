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
use Spiral\Session\Configs\SessionConfig;
use Spiral\Session\Exceptions\MultipleSessionException;
use Spiral\Session\Exceptions\SessionException;

/**
 * Initiates session instance and configures session handlers.
 */
class SessionFactory extends Component implements SingletonInterface
{
    /**
     * @var \Spiral\Session\Configs\SessionConfig
     */
    private $config;

    /**
     * @var \Spiral\Core\FactoryInterface
     */
    private $factory;

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
     * @param string $clientSignature User specific token, does not provide full security but
     *                                hardens session transfer.
     * @param string $id              When null - expect php to create session automatically.
     *
     * @return \Spiral\Session\SessionInterface
     *
     * @throws \Spiral\Session\Exceptions\MultipleSessionException
     */
    public function initSession(string $clientSignature, string $id = null): SessionInterface
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
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

        return $this->factory->make(
            SessionInterface::class,
            compact('clientSignature', 'id')
        );
    }

    /**
     * @param string $handler
     *
     * @return mixed|null|object
     */
    protected function initHandler(string $handler)
    {
        //Init handler
        return $this->factory->make(
            $this->config->handlerClass($handler),
            $this->config->handlerOptions($handler)
        );
    }
}