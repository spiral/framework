<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

use Spiral\Core\BootloadManager;
use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Modules\Configs\ModulesConfig;

/**
 * ModulesManager used to manager external spiral packages (modules) including their installation,
 * resource updates and etc.
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
    private $config = null;

    /**
     * Names of classes loaded in a current session. Module manager allowed to installer and publish
     * only this set of modules.
     *
     * @var array
     */
    private $loadedClasses = [];

    /**
     * @param ModulesConfig   $config
     * @param BootloadManager $bootloader
     */
    public function __construct(ModulesConfig $config, BootloadManager $bootloader)
    {
        $this->config = $config;
        $this->loadedClasses = $bootloader->getClasses();
    }

    /**
     * List of modules associated with their registration status. [module => bool]
     *
     * @return array
     */
    public function getModules()
    {
        $result = [];
        foreach ($this->loadedClasses as $class) {
            $reflection = new \ReflectionClass($class);

            if (!$reflection->isSubclassOf(ModuleInterface::class)) {
                continue;
            }

            $result[$reflection->getName()] = in_array(
                $reflection->getName(),
                $this->config->getModules()
            );
        }

        return $result;
    }
}