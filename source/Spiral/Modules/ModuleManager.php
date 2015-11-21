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
use Spiral\Core\HippocampusInterface;
use Spiral\Modules\Configs\ModulesConfig;
use Spiral\Modules\Interfaces\PublishesInterface;

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
     * Modules cache.
     */
    const MEMORY = 'modules';

    /**
     * Cached modules initialization schema.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * @param ModulesConfig        $config
     * @param HippocampusInterface $memory
     * @param ContainerInterface   $container
     */
    public function __construct(
        ModulesConfig $config,
        HippocampusInterface $memory,
        ContainerInterface $container
    ) {
        $this->config = $config;
        $this->container = $container;

        //Loading modules schema from cache
        $this->schema = $memory->loadData(static::MEMORY);

        if (empty($schema) || $schema['snapshot'] != $config->getModules()) {

            //Schema expired or empty
            $this->schema = $this->generateSchema($container, $config);
            $memory->saveData(static::MEMORY, $this->schema);

            return;
        }

        //We can initiate schema thought the cached schema
        $this->initModules($container);
    }

    /**
     * Publish registered module resources.
     *
     * @param ContainerInterface $container
     * @param PublisherInterface $publisher
     */
    public function publishResources(ContainerInterface $container, PublisherInterface $publisher)
    {
        foreach ($this->schema['publishable'] as $module) {
            /**
             * @var PublishesInterface $object
             */
            $object = $container->get($module);

            //Publish (call is reverted for logging purposes)!
            $publisher->publish($object);
        }
    }

    /**
     * Init modules based on cached schema.
     *
     * @param ContainerInterface $container
     */
    protected function initModules(ContainerInterface $container)
    {
        foreach ($this->schema['modules'] as $module) {
            if (!$module['static']) {
                $object = $container->get($module['class']);

                if ($module['bootload']) {
                    $bootload = new \ReflectionMethod($object, 'bootload');

                    //Bootloading!
                    $bootload->invokeArgs($object, $container->resolveArguments($bootload));
                }

                continue;
            }

            //Module is static, meaning we only have string additive bindings
            foreach ($module['bindings'] as $alias => $resolver) {
                $container->bind($alias, $resolver);
            }
        }
    }

    /**
     * Generate behaviour schema for ModuleManager.
     *
     * @param ContainerInterface $container
     * @param ModulesConfig      $config
     * @return array
     */
    protected function generateSchema(ContainerInterface $container, ModulesConfig $config)
    {
        $schema = [
            //State of modules at moment of generation
            'snapshot'    => $config->getModules(),
            //Modules to be loaded in static mode or bootloaded
            'modules'     => [],
            'publishable' => []
        ];

        foreach ($config->getModules() as $module) {
            //We have to do something here
            echo 1;
        }

        return $schema;
    }
}