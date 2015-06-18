<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage;

use Spiral\Core\Component;

class StorageObject extends Component
{
    /**
     * Full object address. Address used to identify associated container using container prefix,
     * address can be either meaningless string or be valid URL, in this case object address can be
     * used as to detect container, as to show on web page.
     *
     * @var string
     */
    protected $address = false;

    /**
     * Storage component.
     *
     * @invisible
     * @var StorageManager
     */
    protected $storage = null;

    /**
     * Associated storage container. Every container represent one "virtual" folder which can be
     * located on local machine, another server (ftp) or in cloud (amazon, rackspace). Container
     * provides basic unified functionality to manage files inside, all low level operations perform
     * by servers (adapters), this technique allows you to create application and code which does not
     * require to specify storage requirements at time of development.
     *
     * @var StorageContainer
     */
    protected $container = null;

    /**
     * Object name is relative name inside one specific container, can include filename and directory
     * name.
     *
     * @var string
     */
    protected $name = false;

    /**
     * Storage objects used to represent one single file located at remote, local or cloud server,
     * such object provides basic set of API required to manager it location or retrieve file content.
     *
     * @param string           $address   Full object address.
     * @param string           $name      Relative object name.
     * @param StorageManager   $storage   Storage component.
     * @param StorageContainer $container Associated storage object.
     * @throws StorageException
     */
    public function __construct(
        $address,
        $name = '',
        StorageManager $storage,
        StorageContainer $container = null
    )
    {
        $this->storage = $storage;

        if (empty($container))
        {
            //We already know address and name
            $this->address = $address;
            $this->container = $container;
            $this->name = $name;

            return;
        }

        //Trying to find container using address
        if (empty($address))
        {
            throw new StorageException("Unable to create StorageObject with empty address.");
        }

        $this->address = $address;
        $this->container = $this->storage->locateContainer($address, $this->name);
    }

    /**
     * Object name is relative name inside one specific container, can include filename and directory
     * name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Full object address. Address used to identify associated container using container prefix,
     * address can be either meaningless string or be valid URL, in this case object address can be
     * used as to detect container, as to show on web page.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Associated storage container. Every container represent one "virtual" folder which can be
     * located on local machine, another server (ftp) or in cloud (amazon, rackspace). Container
     * provides basic unified functionality to manage files inside, all low level operations perform
     * by servers (adapters), this technique allows you to create application and code which does not
     * require to specify storage requirements at time of development.
     *
     * @return StorageContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Check if given object (name) exists in associated container.
     *
     * @return bool
     */
    public function isExists()
    {
        if (empty($this->name))
        {
            return false;
        }

        return $this->container->exists($this->name);
    }

    /**
     * Retrieve object size in bytes, should return 0 if object not exists.
     *
     * @return int
     */
    public function getSize()
    {
        if (empty($this->name))
        {
            return false;
        }

        return $this->container->getSize($this->name);
    }

    /**
     * Allocate local filename for remove storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename.
     *
     * @return string
     */
    public function getFilename()
    {
        if (empty($this->name))
        {
            return '';
        }

        return $this->container->allocateFilename($this->name);
    }

    /**
     * Remove storage object without changing it's own container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * @param string $newName
     * @return bool|StorageObject
     */
    public function rename($newName)
    {
        if (empty($this->name))
        {
            return false;
        }

        $this->address = $this->container->rename($this->name, $newName);
        if (!empty($this->address))
        {
            $this->name = $newName;

            return $this;
        }

        return false;
    }

    /**
     * Delete storage object from associated container.
     */
    public function delete()
    {
        if (empty($this->name))
        {
            return;
        }

        $this->container->delete($this->name);

        $this->address = $this->name = '';
        $this->container = null;
    }

    /**
     * Copy object to another internal (under save server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $destination Destination container (under same server).
     * @return StorageObject
     * @throws StorageException
     */
    public function copy($destination)
    {
        if (empty($this->name))
        {
            return false;
        }

        if (is_string($destination))
        {
            $destination = $this->storage->container($destination);
        }

        return $this->container->copy($destination, $this->name);
    }

    /**
     * Move object to another internal (under save server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * Will return replaced object address if success.
     *
     * @param StorageContainer $destination Destination container (under same server).
     * @return bool|StorageObject
     * @throws StorageException
     */
    public function replace($destination)
    {
        if (empty($this->name))
        {
            return false;
        }

        if (is_string($destination))
        {
            $destination = $this->storage->container($destination);
        }

        $this->address = $this->container->replace($destination, $this->name);
        if (!empty($this->address))
        {
            $this->container = $destination;

            return $this;
        }

        return false;
    }

    /**
     * Serialize storage object to string (full object address).
     *
     * @return string
     */
    public function __toString()
    {
        return $this->address;
    }

    /**
     * Create StorageObject based on provided address, object name and container will be detected
     * automatically using prefix encoded in address.
     *
     * @param string $address Object address with name and container prefix.
     * @return StorageObject
     */
    public static function open($address)
    {
        return StorageManager::getInstance()->open($address);
    }
}