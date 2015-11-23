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
     * Memory section. Little bit hardcoded at this moment.
     */
    const MEMORY = 'bootloading';

    /**
     * Components/initializers to be autoloader while application initialization.
     *
     * @var array
     */
    private $bootloaders = [];

    /**
     * @invisible
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * @param array              $bootloaders
     * @param ContainerInterface $container
     */
    public function __construct(array $bootloaders, ContainerInterface $container)
    {
        $this->bootloaders = $bootloaders;
        $this->container = $container;
    }

    /**
     * Initiate all given service providers. Has ability to cache list in memory.
     *
     * @param HippocampusInterface|null $memory
     */
    public function bootload(HippocampusInterface $memory = null)
    {
        if (!empty($memory)) {
            $schema = $memory->loadData(static::MEMORY);
        }

        if (empty($schema) || $schema['snapshot'] != $this->bootloaders) {
            //Schema expired or empty
            $schema = $this->generateSchema();

            if (!empty($memory)) {
                $memory->saveData(static::MEMORY, $schema);
            }

            return;
        }

        //We can initiate schema thought the cached schema
        $this->schematicBootload($schema);
    }

    /**
     * Bootload based on schema.
     *
     * @param array $schema
     */
    protected function schematicBootload(array $schema)
    {
        foreach ($schema['bootloaders'] as $bootloader => $options) {
            if (array_key_exists('bindings', $options)) {
                $this->initBindings($options);
            }

            if ($options['init']) {
                $object = $this->container->get($bootloader);

                //Booting
                if ($options['boot']) {
                    $boot = new \ReflectionMethod($object, 'boot');
                    $boot->invokeArgs($object, $this->container->resolveArguments($boot));
                }
            }
        }
    }

    /**
     * Generate cached bindings schema.
     *
     * @return array
     */
    protected function generateSchema()
    {
        $schema = [
            'snapshot'    => $this->bootloaders,
            'bootloaders' => []
        ];

        foreach ($this->bootloaders as $bootloaders) {
            $initSchema = ['init' => true, 'boot' => false];

            $object = $this->container->get($bootloaders);

            if ($object instanceof BootloaderInterface) {
                $initSchema['bindings'] = $object->defineBindings();
                $initSchema['singletons'] = $object->defineSingletons();

                $reflection = new \ReflectionClass($object);

                //Can be booted based on it's configuration
                $initSchema['boot'] = (bool)$reflection->getConstant('BOOT');
                $initSchema['init'] = $initSchema['boot'];

                //Let's initialize now
                $this->initBindings($initSchema);
            } else {
                $initSchema['init'] = true;
            }

            //Need more checks here
            if ($initSchema['boot']) {
                $boot = new \ReflectionMethod($object, 'boot');
                $boot->invokeArgs($object, $this->container->resolveArguments($boot));
            }

            $schema['bootloaders'][$bootloaders] = $initSchema;
        }

        return $schema;
    }

    /**
     * Bind declared bindings.
     *
     * @param array $bootSchema
     */
    protected function initBindings(array $bootSchema)
    {
        foreach ($bootSchema['bindings'] as $aliases => $resolver) {
            $this->container->bind($aliases, $resolver);
        }

        foreach ($bootSchema['singletons'] as $aliases => $resolver) {
            $this->container->bindSingleton($aliases, $resolver);
        }
    }
}