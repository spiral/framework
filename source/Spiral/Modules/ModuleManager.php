<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Modules\Configs\ModulesConfig;

/**
 * ModulesManager used to manager external spiral packages (modules) their bootstrapping and
 * installation. List of modules to be bootstrapped are located in modules config.
 */
class ModuleManager extends Component implements SingletonInterface
{
    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * @var ModulesConfig
     */
    protected $config = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ModulesConfig      $config
     * @param ContainerInterface $container
     */
    public function __construct(ModulesConfig $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;

        $this->bootstrap();
    }

    /**
     * Initiate modules and their bindings.
     */
    protected function bootstrap()
    {

    }
}