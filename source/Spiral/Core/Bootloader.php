<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\ServiceProviders\BootableInterface;
use Spiral\Core\ServiceProviders\ServiceProviderInterface;

/**
 * Provides ability to bootload ServiceProviders.
 */
class Bootloader
{
    /**
     * Memory section.
     */
    const MEMORY = 'bootloading';

    /**
     * Components to be autoloader while application initialization.
     *
     * @var array
     */
    private $serviceProviders = [];

    /**
     * @invisible
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * @param array              $serviceProviders
     * @param ContainerInterface $container
     */
    public function __construct(array $serviceProviders, ContainerInterface $container)
    {
        $this->serviceProviders = $serviceProviders;
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

        if (empty($schema) || $schema['snapshot'] != $this->serviceProviders) {
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
        foreach ($schema['serviceProviders'] as $serviceProvider => $options) {
            if (array_key_exists('bindings', $options)) {
                $this->initBindings($options);
            }

            if ($options['init']) {
                $object = $this->container->get($serviceProvider);

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
            'snapshot'         => $this->serviceProviders,
            'serviceProviders' => []
        ];

        foreach ($this->serviceProviders as $serviceProvider) {
            $providerSchema = [
                'init' => true,
                'boot' => false
            ];

            $object = $this->container->get($serviceProvider);

            if ($object instanceof ServiceProviderInterface) {
                $providerSchema['bindings'] = $object->defineBindings();
                $providerSchema['singletons'] = $object->defineSingletons();
                $providerSchema['injectors'] = $object->defineInjectors();

                $reflection = new \ReflectionClass($object);

                //Can be booted based on it's configuration
                $providerSchema['boot'] = (bool)$reflection->getConstant('BOOT');
                $providerSchema['init'] = $providerSchema['boot'];

                //Let's initialize now
                $this->initBindings($providerSchema);
            } else {
                $providerSchema['init'] = true;
            }

            //Need more checks here
            if ($providerSchema['boot']) {
                $boot = new \ReflectionMethod($object, 'boot');
                $boot->invokeArgs($object, $this->container->resolveArguments($boot));
            }

            $schema['serviceProviders'][$serviceProvider] = $providerSchema;
        }

        return $schema;
    }

    /**
     * Bind declared bindings.
     *
     * @param array $providerSchema
     */
    protected function initBindings(array $providerSchema)
    {
        foreach ($providerSchema['bindings'] as $aliases => $resolver) {
            $this->container->bind($aliases, $resolver);
        }

        foreach ($providerSchema['singletons'] as $aliases => $resolver) {
            $this->container->bindSingleton($aliases, $resolver);
        }

        foreach ($providerSchema['injectors'] as $aliases => $injector) {
            $this->container->bindInjector($aliases, $injector);
        }
    }
}