<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreException;
use Spiral\Support\Generators\Config\ConfigWriter;

class ModuleManager extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Container instance.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * List of registered modules, their event associations, bootstraps and view namespaces.
     *
     * @var array
     */
    protected $modules = [];

    /**
     * Modules component is always initiated and used to support external packages. Constructing
     * modules will ensure all requested bindings mounted and packages initiated via calling module
     * bootstrap() method.
     *
     * @param ConfiguratorInterface $configurator
     * @param Container             $container
     * @throws CoreException
     */
    public function __construct(ConfiguratorInterface $configurator, Container $container)
    {
        $this->container = $container;

        $this->modules = $configurator->getConfig('modules');
        if (!empty($this->modules))
        {
            foreach ($this->modules as $module)
            {
                foreach ($module['bindings'] as $alias => $resolver)
                {
                    $this->container->bind($alias, $resolver);
                }

                if ($module['bootstrap'])
                {
                    //Some modules may request initialization
                    $this->container->get($module['class'], compact('core'))->bootstrap();
                }
            }
        }
    }

    /**
     * Get all installed and registered modules.
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Checking if module is registered in system and return it's configuration if does.
     *
     * @param string $module
     * @return bool|array
     */
    public function hasModule($module)
    {
        return array_key_exists($module, $this->modules) ? $this->modules[$module] : false;
    }

    /**
     * Find all available modules classes and return their definitions. Definitions will be sorted
     * in order of required dependencies.
     *
     * @return Definition[]
     */
    public function findModules()
    {
        $definitions = [];

        //Delayed and not included to constructor dependencies to speed up application.
        $classes = Tokenizer::getInstance($this->container)->getClasses(
            'Spiral\Components\Modules\ModuleInterface'
        );

        foreach ($classes as $module)
        {
            if ($module['abstract'])
            {
                continue;
            }

            $definition = call_user_func([$module['name'], 'getDefinition']);
            $definitions[$definition->getName()] = $definition;
        }

        //Sorting based on dependencies
        uasort($definitions, function (Definition $a, Definition $b)
        {
            return !in_array($a->getName(), $b->getDependencies());
        });

        return $definitions;
    }

    /**
     * Registering module definition, all module start up requirements will be copied into modules
     * configuration and will be performed during modules component initialization.
     *
     * @param Definition $definition
     */
    public function registerModule(Definition $definition)
    {
        $this->modules[$definition->getName()] = [
            'class'     => $definition->getClass(),
            'bootstrap' => $definition->getInstaller()->isBootstrappable(),
            'bindings'  => $definition->getInstaller()->getBindings()
        ];

        //Updating modules configuration config
        $this->updateConfig();
    }

    /**
     * Refresh modules configuration file with only existed modules and their configurations. Should
     * be called every time new module gets registered or removed.
     */
    protected function updateConfig()
    {
        foreach ($this->modules as $name => $module)
        {
            try
            {
                if (!class_exists($module['class']))
                {
                    unset($this->modules[$name]);
                }
            }
            catch (CoreException $exception)
            {
                unset($this->modules[$name]);
            }
        }

        //Updating configuration
        $config = ConfigWriter::make([
            'name'   => 'modules',
            'method' => ConfigWriter::OVERWRITE
        ],
            $this->container
        );

        //Little-bit dirty, but it should spiral application environment for now...
        $config->setConfig($this->modules)->writeConfig(
            directory('config'),
            FileManager::READONLY
        );
    }
}