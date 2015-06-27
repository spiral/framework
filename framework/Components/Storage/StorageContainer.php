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
use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Container\InjectableInterface;

class StorageContainer extends Component implements InjectableInterface
{
    /**
     * InjectableInterface declares to spiral Container that requested interface or class should
     * not be resolved using default mechanism. Following interface does not require any methods,
     * however class or other interface which inherits InjectableInterface should declare constant
     * named "INJECTION_MANAGER" with name of class responsible for resolving that injection.
     *
     * InjectionFactory will receive requested class or interface reflection and reflection linked
     * to parameter in constructor or method used to declare injection.
     */
    const INJECTION_MANAGER = 'Spiral\Components\Storage\StorageManager';

    /**
     * Address prefix will be attached to all container objects to generate unique object address.
     * You can use domain name, or folder for prefixed which should represent public containers, in
     * this case object address will be valid URL.
     *
     * @var string
     */
    public $prefix = '';

    /**
     * Associated server name or id. Every server represent one virtual storage which can be either
     * local, remove or cloud based. Every adapter should support basic set of low-level operations
     * (create, move, copy and etc). Adapter instance called server, one adapter can be used for
     * multiple servers.
     *
     * @var string
     */
    protected $server = '';

    /**
     * Container options vary based on server (adapter) type associated, for local and ftp it usually
     * folder name and file permissions, for cloud or remove storages - remote bucket name and access
     * mode.
     *
     * @var array
     */
    public $options = array();

    /**
     * Storage component.
     *
     * @invisible
     * @var StorageManager
     */
    protected $storage = null;

    /**
     * FileManager component.
     *
     * @invisible
     * @var FileManager
     */
    protected $file = null;

    /**
     * Every container represent one "virtual" folder which can be located on local machine, another
     * server (ftp) or in cloud (amazon, rackspace). Container provides basic unified functionality
     * to manage files inside, all low level operations perform by servers (adapters), this technique
     * allows you to create application and code which does not require to specify storage requirements
     * at time of development.
     *
     * @param string         $server  Responsible server id or name.
     * @param string         $prefix  Addresses prefix.
     * @param array          $options Server related options.
     * @param StorageManager $storage StorageManager component.
     * @param FileManager    $file    FileManager component.
     */
    public function __construct(
        $server,
        $prefix,
        array $options,
        StorageManager $storage,
        FileManager $file
    )
    {
        $this->prefix = $prefix;
        $this->server = $server;
        $this->options = $options;
        $this->storage = $storage;
        $this->file = $file;
    }

    /**
     * Get associated storage server. Every server represent one virtual storage which can be either
     * local, remove or cloud based. Every adapter should support basic set of low-level operations
     * (create, move, copy and etc). Adapter instance called server, one adapter can be used for
     * multiple servers.
     *
     * @return StorageServerInterface
     */
    public function getServer()
    {
        return $this->storage->server($this->server);
    }

    /**
     * Check if object with given address can be potentially located inside this container and return
     * prefix length.
     *
     * @param string $address Object address.
     * @return bool|int
     */
    public function checkPrefix($address)
    {
        if (strpos($address, $this->prefix) === 0)
        {
            return strlen($this->prefix);
        }

        return false;
    }

    /**
     * Build object address using object name and container prefix. While using URL like prefixes
     * address can appear valid URI which can be used directly at frontend.
     *
     * @param string $name
     * @return string
     */
    public function buildAddress($name)
    {
        return $this->prefix . $name;
    }

    /**
     * Check if given object (name) exists in current container.
     *
     * @param string $name Relative object name.
     * @return bool
     */
    public function isExists($name)
    {
        $this->log("Check '{$this->buildAddress($name)}' exists at '{$this->server}'.");

        benchmark("{$this->server}::exists", $this->prefix . $name);
        $result = $this->getServer()->isExists($this, $name);
        benchmark("{$this->server}::exists", $this->prefix . $name);

        return (bool)$result;
    }

    /**
     * Retrieve object size in bytes, should return 0 if object not exists.
     *
     * @param string $name Relative object name.
     * @return int|bool
     */
    public function getSize($name)
    {
        $this->log("Get size of '{$this->buildAddress($name)}' at '{$this->server}'.");

        benchmark("{$this->server}::size", $this->prefix . $name);
        $filesize = $this->getServer()->getSize($this, $name);
        benchmark("{$this->server}::size", $this->prefix . $name);

        return $filesize;
    }

    /**
     * Upload new storage object using given filename or stream.
     *
     * @param string                                       $name     Relative object name.
     * @param string|StreamInterface|UploadedFileInterface $origin   Local filename or stream to use
     *                                                               for creation.
     * @return StorageObject
     * @throws StorageException
     */
    public function upload($name, $origin)
    {
        $this->log("Upload '{$this->buildAddress($name)}' at '{$this->server}' server.");

        if ($origin instanceof UploadedFileInterface)
        {
            //Known simplification for UploadedFile
            $origin = $origin->getStream();
        }

        benchmark("{$this->server}::upload", $this->prefix . $name);
        if ($this->getServer()->upload($this, $name, $origin))
        {
            benchmark("{$this->server}::upload", $this->prefix . $name);

            return new StorageObject($this->buildAddress($name), $name, $this->storage, $this);
        }

        benchmark("{$this->server}::upload", $this->prefix . $name);
        throw new StorageException(
            "Unable to upload '{$this->buildAddress($name)}' at '{$this->server}' server."
        );
    }

    /**
     * Allocate local filename for remove storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. File is in readonly
     * mode, and in some cases will be erased on shutdown.
     *
     * @param string $name Relative object name.
     * @return string
     */
    public function allocateFilename($name)
    {
        $this->log("Get local filename of '{$this->buildAddress($name)}' at '{$this->server}' server.");

        benchmark("{$this->server}::filename", $this->prefix . $name);
        $filename = $this->getServer()->allocateFilename($this, $name);
        benchmark("{$this->server}::filename", $this->prefix . $name);

        return $filename;
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very identical
     * to localFilename, however in some cases it may store data content in memory simplifying
     * development.
     *
     * @param string $name Relative object name.
     * @return StreamInterface
     */
    public function getStream($name)
    {
        $this->log("Get stream for '{$this->buildAddress($name)}' at '{$this->server}' server.");

        benchmark("{$this->server}::stream", $this->prefix . $name);
        if (!$stream = $this->getServer()->getStream($this, $name))
        {
            //exception
        }

        benchmark("{$this->server}::stream", $this->prefix . $name);

        return $stream;
    }

    /**
     * Remove storage object without changing it's own container. This operation does not require
     * object recreation or download and can be performed on remote server. Will return renamed object
     * address if success.
     *
     * @param string $oldname Relative object name.
     * @param string $newname New object name.
     * @return string
     * @throws StorageException
     */
    public function rename($oldname, $newname)
    {
        if ($oldname == $newname)
        {
            return true;
        }

        benchmark("{$this->server}::rename", $this->prefix . $oldname);
        if ($this->getServer()->rename($this, $oldname, $newname))
        {
            benchmark("{$this->server}::rename", $this->prefix . $oldname);
            $this->log(
                "Rename '{$this->buildAddress($oldname)}' "
                . "to '{$this->buildAddress($newname)}' at '{$this->server}' server."
            );

            return $this->buildAddress($newname);
        }
        benchmark("{$this->server}::rename", $this->prefix . $oldname);

        throw new StorageException(
            "Unable to rename '{$this->buildAddress($oldname)}' "
            . "to '{$this->buildAddress($newname)}' at '{$this->server}' server."
        );
    }

    /**
     * Delete storage object from specified container.
     *
     * @param string $name Relative object name.
     */
    public function delete($name)
    {
        $this->log("Delete '{$this->buildAddress($name)}' at '{$this->server}' server.");

        benchmark("{$this->server}::delete", $this->prefix . $name);
        $this->getServer()->delete($this, $name);
        benchmark("{$this->server}::delete", $this->prefix . $name);
    }

    /**
     * Copy object to another internal (under save server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
     * @return StorageObject
     * @throws StorageException
     */
    public function copy(StorageContainer $destination, $name)
    {
        if ($destination == $this)
        {
            return new StorageObject($this->buildAddress($name), $name, $this->storage, $this);
        }

        //Internal copying
        if ($this->server == $destination->server)
        {
            benchmark("{$this->server}::copy", $this->prefix . $name);
            if ($this->getServer()->copy($this, $destination, $name))
            {
                benchmark("{$this->server}::copy", $this->prefix . $name);
                $this->log(
                    "Internal copy '{$this->buildAddress($name)}' "
                    . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
                );

                return new StorageObject(
                    $destination->buildAddress($name),
                    $name,
                    $this->storage,
                    $destination
                );
            }

            benchmark("{$this->server}::copy", $this->prefix . $name);
            throw new StorageException(
                "Unable to copy '{$this->buildAddress($name)}' "
                . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
            );
        }

        /**
         * Now we will try to copy object using current server/memory as a buffer.
         */
        $stream = $this->getStream($name);
        if ($stream && $destination->upload($name, $stream))
        {
            $this->log(
                "External copy '{$this->server}'.'{$this->buildAddress($name)}' "
                . "to '{$destination->server}'.'{$destination->buildAddress($name)}'."
            );

            return new StorageObject(
                $destination->buildAddress($name),
                $name,
                $this->storage,
                $destination
            );
        }

        throw new StorageException(
            "Unable to copy '{$this->server}'.'{$this->buildAddress($name)}' "
            . "to '{$destination->server}'.'{$destination->buildAddress($name)}'."
        );
    }

    /**
     * Move object to another internal (under save server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * Will return replaced object address if success.
     *
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
     * @return string
     * @throws StorageException
     */
    public function replace(StorageContainer $destination, $name)
    {
        if ($destination == $this)
        {
            return $this->buildAddress($name);
        }

        //Internal copying
        if ($this->server == $destination->server)
        {
            benchmark("{$this->server}::replace", $this->prefix . $name);
            if ($this->getServer()->replace($this, $destination, $name))
            {
                benchmark("{$this->server}::replace", $this->prefix . $name);
                $this->log(
                    "Internal move '{$this->buildAddress($name)}' "
                    . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
                );

                return $destination->buildAddress($name);
            }

            benchmark("{$this->server}::replace", $this->prefix . $name);
            throw new StorageException(
                "Unable to move '{$this->buildAddress($name)}' "
                . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
            );
        }

        /**
         * Now we will try to replace object using current server/memory as a buffer.
         */
        $stream = $this->getStream($name);
        if ($stream && $destination->upload($name, $stream))
        {
            $this->log(
                "External move '{$this->server}'.'{$this->buildAddress($name)}'"
                . " to '{$destination->server}'.'{$destination->buildAddress($name)}'."
            );

            $stream->detach() && $this->delete($name);

            return $destination->buildAddress($name);
        }

        throw new StorageException(
            "Unable to move '{$this->server}'.'{$this->buildAddress($name)}' "
            . "to '{$destination->server}'.'{$destination->buildAddress($name)}'."
        );
    }

    /**
     * Helper logger method. Will use info log level for all messages.
     *
     * @param string $message
     * @param array  $context
     */
    protected function log($message, $context = array())
    {
        StorageManager::logger()->info($message, $context);
    }
}