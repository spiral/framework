<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Bootloaders\BootloaderInterface;

/**
 * Provides ability to bootload ServiceProviders.
 */
class BootloadProcessor
{
    /**
     * Components/initializers to be autoloader while application initialization.
     *
     * @var array
     */
    private $bootloaders = [];

    /**
     * @invisible
     * @var HippocampusInterface
     */
    protected $memory = null;


    /**
     * @param array                $bootloaders
     * @param HippocampusInterface $memory
     */
    public function __construct(array $bootloaders, HippocampusInterface $memory)
    {
        $this->bootloaders = $bootloaders;
        $this->memory = $memory;
    }

    /**
     * Initiate all given service providers. Has ability to cache list in memory.
     *
     * @param ContainerInterface $container
     * @param string|null        $memory Memory section to be used for caching, set to null to
     *                                   disable caching.
     */
    public function bootload(ContainerInterface $container, $memory = null)
    {
        if (!empty($memory)) {
            $schema = $this->memory->loadData($memory);
        }

        if (empty($schema) || $schema['snapshot'] != $this->bootloaders) {
            //Schema expired or empty
            $schema = $this->generateSchema($container);

            if (!empty($memory)) {
                $this->memory->saveData($memory, $schema);
            }

            return;
        }

        //We can initiate schema thought the cached schema
        $this->schematicBootload($container, $schema);
    }

    /**
     * Bootload based on schema.
     *
     * @param ContainerInterface $container
     * @param array              $schema
     */
    protected function schematicBootload(ContainerInterface $container, array $schema)
    {
        foreach ($schema['bootloaders'] as $bootloader => $options) {
            if (array_key_exists('bindings', $options)) {
                $this->initBindings($container, $options);
            }

            if ($options['init']) {
                $object = $container->get($bootloader);

                //Booting
                if ($options['boot']) {
                    $boot = new \ReflectionMethod($object, 'boot');
                    $boot->invokeArgs($object, $container->resolveArguments($boot));
                }
            }
        }
    }

    /**
     * Generate cached bindings schema.
     *
     * @param ContainerInterface $container
     * @return array
     */
    protected function generateSchema(ContainerInterface $container)
    {
        $schema = [
            'snapshot'    => $this->bootloaders,
            'bootloaders' => []
        ];

        foreach ($this->bootloaders as $bootloaders) {
            $initSchema = ['init' => true, 'boot' => false];

            $object = $container->get($bootloaders);

            if ($object instanceof BootloaderInterface) {
                $initSchema['bindings'] = $object->defineBindings();
                $initSchema['singletons'] = $object->defineSingletons();

                $reflection = new \ReflectionClass($object);

                //Can be booted based on it's configuration
                $initSchema['boot'] = (bool)$reflection->getConstant('BOOT');
                $initSchema['init'] = $initSchema['boot'];

                //Let's initialize now
                $this->initBindings($container, $initSchema);
            } else {
                $initSchema['init'] = true;
            }

            //Need more checks here
            if ($initSchema['boot']) {
                $boot = new \ReflectionMethod($object, 'boot');
                $boot->invokeArgs($object, $container->resolveArguments($boot));
            }

            $schema['bootloaders'][$bootloaders] = $initSchema;
        }

        return $schema;
    }

    /**
     * Bind declared bindings.
     *
     * @param ContainerInterface $container
     * @param array              $bootSchema
     */
    protected function initBindings(ContainerInterface $container, array $bootSchema)
    {
        foreach ($bootSchema['bindings'] as $aliases => $resolver) {
            $container->bind($aliases, $resolver);
        }

        foreach ($bootSchema['singletons'] as $aliases => $resolver) {
            $container->bindSingleton($aliases, $resolver);
        }
    }
}