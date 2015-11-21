<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Tokenizer\TokenizerInterface;

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
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ConfiguratorInterface $configurator
     * @param ContainerInterface    $container
     */
    public function __construct(ConfiguratorInterface $configurator, ContainerInterface $container)
    {
        $this->modules = $configurator->getConfig('modules');
        $this->container = $container;

        foreach ($this->modules as $module) {
            foreach ($module['bindings'] as $alias => $resolver) {
                $this->container->bind($alias, $resolver);
            }

            //Some modules may request initialization
            if ($module['bootstrap']) {
                $this->container->get($module['class'])->bootstrap();
            }
        }
    }

    /**
     * Checking if module was already registered in modules config.
     *
     * @param string $module
     * @return bool
     */
    public function hasModule($module)
    {
        return (bool)array_key_exists($module, $this->modules);
    }

    /**
     * Get every installed and registered modules.
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Find every available module return it's definitions. Definitions must be sorted in order of
     * required dependencies.
     *
     * @param TokenizerInterface $tokenizer
     * @return DefinitionInterface[]
     */
    public function findModules(TokenizerInterface $tokenizer = null)
    {
        $tokenizer = !empty($tokenizer) ? $tokenizer : $this->container->get(TokenizerInterface::class);

        $definitions = [];
        foreach ($tokenizer->getClasses(BootloadableInterface::class) as $module) {
            if ($module['abstract']) {
                continue;
            }

            //Every ModuleInterface going to declare static method getDefinition
            $definition = call_user_func([$module['name'], 'getDefinition'], $this->container);
            $definitions[$definition->getName()] = $definition;
        }

        //Sorting based on dependencies
        uasort($definitions, function (DefinitionInterface $moduleA, DefinitionInterface $moduleB) {
            return !in_array($moduleA->getName(), $moduleB->getDependencies());
        });

        return $definitions;
    }

    /**
     * Register module by it's definition, all module start up requirements (bindings, bootstrap)
     * will be copied into modules configuration and be performed when ModuleManager started.
     *
     * @param DefinitionInterface $definition
     */
    public function registerModule(DefinitionInterface $definition)
    {
        $this->modules[$definition->getName()] = [
            'class'     => $definition->getClass(),
            'bootstrap' => $definition->getInstaller()->needsBootstrapping(),
            'bindings'  => $definition->getInstaller()->getBindings()
        ];

        //Update modules configuration config
        $this->updateConfig();
    }

    /**
     * Refresh modules configuration file with only existed modules and their configurations.
     * Should be called every time new module gets registered or removed.
     */
    private function updateConfig()
    {
        foreach ($this->modules as $name => $module) {
            if (!class_exists($module['class'])) {
                //Module got removed
                unset($this->modules[$name]);
            }
        }

        /**
         * We are going to store information about modules into component configuration
         *
         * @var ConfigWriter $configWriter
         */
        $configWriter = $this->container->construct(ConfigWriter::class, [
            'name'   => 'modules',
            'method' => ConfigWriter::FULL_OVERWRITE
        ]);

        //Writing (make sure we are inside environment with right permissions)
        $configWriter->setConfig($this->modules)->writeConfig();
    }
}