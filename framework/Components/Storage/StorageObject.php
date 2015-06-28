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

        if (!empty($container))
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
     * Check if object exists in associated container. Method should never fail if file not exists
     * and will return bool in any condition.
     *
     * @return bool
     */
    public function isExists()
    {
        if (empty($this->name))
        {
            return false;
        }

        return $this->container->isExists($this->name);
    }

    /**
     * Retrieve object size in bytes, should return false if object does not exists.
     *
     * @return int|bool
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
     * Allocate local filename for remote storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. File is in readonly
     * mode, and in some cases will be erased on shutdown.
     *
     * @return string
     * @throws StorageException
     */
    public function localFilename()
    {
        if (empty($this->name))
        {
            throw new StorageException("Unable to allocate filename for unassigned storage object.");
        }

        return $this->container->allocateFilename($this->name);
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very similar
     * to localFilename, however in some cases it may store data content in memory.
     *
     * @return StreamInterface
     * @throws StorageException
     */
    public function getStream()
    {
        if (empty($this->name))
        {
            throw new StorageException("Unable to get stream for unassigned storage object.");
        }

        return $this->container->getStream($this->name);
    }

    /**
     * Rename storage object without changing it's container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * @param string $newname New storage object name.
     * @return StorageObject
     * @throws StorageException
     */
    public function rename($newname)
    {
        if (empty($this->name))
        {
            throw new StorageException("Unable to rename unassigned storage object.");
        }

        $this->address = $this->container->rename($this->name, $newname);
        $this->name = $newname;

        return $this;
    }

    /**
     * Delete storage object from associated container. Method should not fail if object does not
     * exists.
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
     * Copy object to another internal (under same server) container, this operation may not
     * require file download and can be performed remotely.
     *
     * Method will return new instance of StorageObject associated with copied data.
     *
     * @param StorageContainer $destination Destination container (under same server).
     * @return bool
     * @throws StorageException
     */
    public function copy($destination)
    {
        if (empty($this->name))
        {
            throw new StorageException("Unable to copy unassigned storage object.");
        }

        if (is_string($destination))
        {
            $destination = $this->storage->container($destination);
        }

        return $this->container->copy($destination, $this->name);
    }

    /**
     * Replace object to another internal (under same server) container, this operation may not
     * require file download and can be performed remotely.
     *
     * Method will return replaced storage object address.
     *
     * @param StorageContainer $destination Destination container (under same server).
     * @return string
     * @throws StorageException
     */
    public function replace($destination)
    {
        if (empty($this->name))
        {
            throw new StorageException("Unable to replace unassigned storage object.");
        }

        if (is_string($destination))
        {
            $destination = $this->storage->container($destination);
        }

        $this->address = $this->container->replace($destination, $this->name);
        $this->container = $destination;

        return $this;
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