<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Core;
use Spiral\Helpers\StringHelper;

class StorageManager extends Component implements Container\InjectionManagerInterface
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait, Component\LoggerTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'storage';

    /**
     * StorageContainer class for reference.
     */
    const CONTAINER = 'Spiral\Components\Storage\StorageContainer';

    /**
     * List of initiated storage containers, every container represent one "virtual" folder which can be located on local
     * machine, another server (ftp) or in cloud (amazon, rackspace). Container provides basic unified functionality to
     * manage files inside, all low level operations perform by servers (adapters), this technique allows you to create
     * application and code which does not require to specify storage requirements at time of
     * development.
     *
     * @var StorageContainer[]
     */
    protected $containers = array();

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based. Every adapter should
     * support basic set of low-level operations (create, move, copy and etc). Adapter instance called server, one adapter
     * can be used for multiple servers.
     *
     * @var ServerInterface[]
     */
    protected $servers = array();

    /**
     * Initiate storage component to load all container and adapters. Storage component commonly used to manage files using
     * "virtual folders" (container) while such "folder" can represent local, remove or cloud file storage. This allows to
     * write more universal scripts, support multiple environments with different container settings and simplify application
     * testing.
     *
     * Storage component is of component which almost did not changed for last 4 years.
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->config = $core->loadConfig('storage');

        //Loading containers
        foreach ($this->config['containers'] as $name => $container)
        {
            //Controllable injection implemented
            $this->containers[$name] = Core::get(self::CONTAINER, $container + array('storage' => $this), null, true);
        }
    }

    /**
     * Create new real-time storage container with specified prefix, server and options. Container prefix will be automatically
     * attached to every object name inside that container to create object address which has to be unique over every other
     * container.
     *
     * @param string $name    Container name used to create or replace objects.
     * @param string $prefix  Prefix will be attached to object name to create unique address.
     * @param string $server  Server name.
     * @param array  $options Additional adapter specific options.
     * @return StorageContainer
     * @throws StorageException
     */
    public function registerContainer($name, $prefix, $server, array $options = array())
    {
        if (isset($this->containers[$name]))
        {
            throw new StorageException("Unable to create container '{$name}', name already taken.");
        }

        $this->logger()->info(
            "New container '{name}' for server '{server}' registered using '{prefix}' prefix.",
            compact('name', 'prefix', 'server', 'options')
        );

        //Controllable injection implemented
        return $this->containers[$name] = Container::get(
            self::CONTAINER,
            compact('prefix', 'server', 'options') + array('storage' => $this),
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
        if (!$container)
        {
            throw new StorageException("Unable to fetch container, name can not be empty.");
        }

        if (isset($this->containers[$container]))
        {
            return $this->containers[$container];
        }

        throw new StorageException("Unable to fetch container '{$container}', no presets found.");
    }

    /**
     * InjectionManager will receive requested class or interface reflection and reflection linked to parameter in constructor
     * or method used to declare dependency.
     *
     * This method can return pre-defined instance or create new one based on requested class, parameter reflection can be
     * used to dynamic class constructing, for example it can define database name or config section should be used to
     * construct requested instance.
     *
     * @param \ReflectionClass     $class
     * @param \ReflectionParameter $parameter
     * @return mixed
     */
    public static function resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter)
    {
        return self::getInstance()->container($parameter->getName());
    }

    /**
     * Resolve container instance using object address, container will be detected by reading it's own prefix from object
     * address. Container with longest detected prefix will be used to represent such object. Make sure you don't have
     * prefix collisions.
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
                if (!$bestContainer || strlen($bestContainer->prefix) < $prefixLength)
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
     * @return ServerInterface
     * @throws StorageException
     */
    public function server($server, array $options = array())
    {
        if (isset($this->servers[$server]))
        {
            return $this->servers[$server];
        }

        if ($options)
        {
            $this->config['servers'][$server] = $options;
        }

        if (!array_key_exists($server, $this->config['servers']))
        {
            throw new StorageException("Undefined storage server '{$server}'.");
        }

        $config = $this->config['servers'][$server];

        return $this->servers[$server] = Core::get($config['class'], $config);
    }

    /**
     * Create new storage object with specified container, object can be created as empty (not supported by some adapters)
     * or using local filename - in this case file WILL BE REPLACED or uploaded by container to it's new location.
     *
     * While object creation original filename, name (no extension) or extension can be embedded to new object name using
     * string interpolation ({name}.{ext}}
     *
     * Example:
     * Storage::create('cloud', $id . '-{name}.{ext}', $filename);
     * Storage::create('cloud', $id . '-upload-{filename}', $filename);
     *
     * @param string|StorageContainer $container Container name, id or instance.
     * @param string                  $name      Object name should be used in container.
     * @param string                  $filename
     * @return StorageObject|bool
     */
    public function create($container, $name, $filename = '')
    {
        if (is_string($container))
        {
            $container = $this->container($container);
        }

        if ($filename)
        {
            $extension = FileManager::getInstance()->extension($filename);
            $name = StringHelper::interpolate($name, array(
                'ext'       => $extension,
                'name'      => substr(basename($filename), 0, -1 * (strlen($extension) + 1)),
                'filename'  => basename($filename),
                'extension' => $extension
            ));
        }

        return $container->create($filename, $name);
    }

    /**
     * Create StorageObject based on provided address, object name and container will be detected automatically using prefix
     * encoded in address.
     *
     * @param string $address Object address with name and container prefix.
     * @return StorageObject
     */
    public function open($address)
    {
        return StorageObject::make(compact('address') + array('storage' => $this, 'container' => null));
    }
}