<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;

class StorageManager extends Component implements Container\InjectionManagerInterface
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait,
        Component\ConfigurableTrait,
        Component\LoggerTrait;

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
     * List of initiated storage containers, every container represent one "virtual" folder which
     * can be located on local machine, another server (ftp) or in cloud (amazon, rackspace). Container
     * provides basic unified functionality to manage files inside, all low level operations perform
     * by servers (adapters), this technique allows you to create application and code which does not
     * require to specify storage requirements at time of
     * development.
     *
     * @var StorageContainer[]
     */
    protected $containers = [];

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based.
     * Every adapter should support basic set of low-level operations (create, move, copy and etc).
     * Adapter instance called server, one adapter can be used for multiple servers.
     *
     * @var StorageServerInterface[]
     */
    protected $servers = [];

    /**
     * Initiate storage component to load all container and adapters. Storage component commonly used
     * to manage files using "virtual folders" (container) while such "folder" can represent local,
     * remove or cloud file storage. This allows to write more universal scripts, support multiple
     * environments with different container settings and simplify application testing.
     *
     * Storage component is of component which almost did not changed for last 4 years but must be
     * updated later to follow latest specs.
     *
     * @param ConfiguratorInterface $configurator
     * @param Container             $container
     */
    public function __construct(ConfiguratorInterface $configurator, Container $container)
    {
        $this->container = $container;
        $this->config = $configurator->getConfig('storage');

        //Loading containers
        foreach ($this->config['containers'] as $name => $container)
        {
            //Controllable injection implemented
            $this->containers[$name] = $this->container->get(
                StorageContainer::class,
                $container + ['storage' => $this],
                null,
                true
            );
        }
    }

    /**
     * Create new real-time storage container with specified prefix, server and options. Container
     * prefix will be automatically attached to every object name inside that container to create
     * object address which has to be unique over every other container.
     *
     * @param string $name    Container name used to create or replace objects.
     * @param string $prefix  Prefix will be attached to object name to create unique address.
     * @param string $server  Server name.
     * @param array  $options Additional adapter specific options.
     * @return StorageContainer
     * @throws StorageException
     */
    public function registerContainer($name, $prefix, $server, array $options = [])
    {
        if (isset($this->containers[$name]))
        {
            throw new StorageException("Unable to create container '{$name}', name already taken.");
        }

        self::logger()->info(
            "New container '{name}' for server '{server}' registered using '{prefix}' prefix.",
            compact('name', 'prefix', 'server', 'options')
        );

        //Controllable injection implemented
        return $this->containers[$name] = $this->container->get(
            StorageContainer::class,
            compact('prefix', 'server', 'options') + ['storage' => $this],
            null,
            true
        );
    }

    /**
     * Get storage container by it's name. Container should exist at that moment.
     *
     * @param string $container Container name or id.
     * @return StorageContainer
     * @throws StorageException
     */
    public function container($container)
    {
        if (empty($container))
        {
            throw new \InvalidArgumentException("Unable to fetch container, name can not be empty.");
        }

        if (isset($this->containers[$container]))
        {
            return $this->containers[$container];
        }

        throw new StorageException("Unable to fetch container '{$container}', no presets found.");
    }

    /**
     * InjectionManager will receive requested class or interface reflection and reflection linked
     * to parameter in constructor or method used to declare dependency.
     *
     * This method can return pre-defined instance or create new one based on requested class, parameter
     * reflection can be used to dynamic class constructing, for example it can define database name
     * or config section should be used to construct requested instance.
     *
     * @param \ReflectionClass     $class
     * @param \ReflectionParameter $parameter
     * @param Container            $container
     * @return mixed
     */
    public static function resolveInjection(
        \ReflectionClass $class,
        \ReflectionParameter $parameter,
        Container $container
    )
    {
        return self::getInstance($container)->container($parameter->getName());
    }

    /**
     * Resolve container instance using object address, container will be detected by reading it's
     * own prefix from object address. Container with longest detected prefix will be used to represent
     * such object. Make sure you don't have prefix collisions.
     *
     * @param string $address Object address with prefix and name.
     * @param string $name    Object name fetched from address.
     * @return StorageContainer
     */
    public function locateContainer($address, &$name = null)
    {
        /**
         * @var StorageContainer $bestContainer
         */
        $bestContainer = null;

        foreach ($this->containers as $container)
        {
            if ($prefixLength = $container->checkPrefix($address))
            {
                if (empty($bestContainer) || strlen($bestContainer->prefix) < $prefixLength)
                {
                    $bestContainer = $container;
                    $name = substr($address, $prefixLength);
                }
            }
        }

        return $bestContainer;
    }

    /**
     * Create and retrieve server instance described in storage config.
     *
     * @param string $server  Server name or id.
     * @param array  $options Server options, required only it not defined in config.
     * @return StorageServerInterface
     * @throws StorageException
     */
    public function server($server, array $options = [])
    {
        if (isset($this->servers[$server]))
        {
            return $this->servers[$server];
        }

        if (!empty($options))
        {
            $this->config['servers'][$server] = $options;
        }

        if (!array_key_exists($server, $this->config['servers']))
        {
            throw new StorageException("Undefined storage server '{$server}'.");
        }

        $config = $this->config['servers'][$server];

        return $this->servers[$server] = $this->container->get($config['class'], $config);
    }

    /**
     * Create new storage object (or update existed) with specified container, object can be created
     * as empty, using local filename, via Stream or using UploadedFile.
     *
     * While object creation original filename, name (no extension) or extension can be embedded to
     * new object name using string interpolation ({name}.{ext}}
     *
     * Example (using Facades):
     * Storage::create('cloud', $id . '-{name}.{ext}', $filename);
     * Storage::create('cloud', $id . '-upload-{filename}', $filename);
     *
     * @param string|StorageContainer                                    $container Container name, id
     *                                                                              or instance.
     * @param string                                                     $name      Object name should
     *                                                                              be used in container.
     * @param string|StreamInterface|UploadedFileInterface|StorageObject $origin    Local filename or
     *                                                                              Stream.
     * @return StorageObject|bool
     */
    public function put($container, $name, $origin = '')
    {
        $container = is_string($container) ? $this->container($container) : $container;

        if (!empty($origin) && is_string($origin))
        {
            $extension = strtolower(pathinfo($origin, PATHINFO_EXTENSION));
            $name = interpolate($name,
                [
                    'ext'       => $extension,
                    'name'      => substr(basename($origin), 0, -1 * (strlen($extension) + 1)),
                    'filename'  => basename($origin),
                    'extension' => $extension
                ]
            );
        }

        return $container->put($name, $origin);
    }

    /**
     * Create StorageObject based on provided address, object name and container will be detected
     * automatically using prefix encoded in address.
     *
     * @param string $address Object address with name and container prefix.
     * @return StorageObject
     */
    public function open($address)
    {
        return StorageObject::make([
            'address'   => $address,
            'storage'   => $this,
            'container' => null
        ], $this->container);
    }
}