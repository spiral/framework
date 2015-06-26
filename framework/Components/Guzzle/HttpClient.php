<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Guzzle;

use GuzzleHttp\Client;
use Spiral\Core\Component\MakeTrait;
use Spiral\Core\Component\SingletonTrait;
use Spiral\Core\ConfiguratorInterface;

class HttpClient extends Client
{
    /**
     * Spiral traits.
     */
    use SingletonTrait, MakeTrait;

    /**
     * This client is singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * New instance of guzzle client with application specific configuration.
     *
     * @param ConfiguratorInterface $configurator
     */
    public function __construct(ConfiguratorInterface $configurator)
    {
        parent::__construct($configurator->getConfig('guzzle'));
    }
}