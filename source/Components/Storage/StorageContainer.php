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
use Spiral\Components\Http\Stream;
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
     * Get container prefix value.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Check if object with given address can be potentially located inside this container and return
     * prefix length.
     *
     * @param string $address Storage object address (including name and prefix).
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
     * Check if given object (name) exists in current container. Method should never fail if file
     * not exists and will return bool in any condition.
     *
     * @param string $name Storage object name.
     * @return bool
     */
    public function exists($name)
    {
        $this->log("Checking existence of '{$this->buildAddress($name)}' at '{$this->server}'.");

        benchmark("{$this->server}::exists", $this->buildAddress($name));
        $result = (bool)$this->getServer()->exists($this, $name);
        benchmark("{$this->server}::exists", $this->buildAddress($name));

        return $result;
    }

    /**
     * Retrieve object size in bytes, should return false if object does not exists.
     *
     * @param string $name Storage object name.
     * @return int|bool
     */
    public function getSize($name)
    {
        $this->log("Getting size of '{$this->buildAddress($name)}' at '{$this->server}'.");

        benchmark("{$this->server}::size", $this->buildAddress($name));
        $size = $this->getServer()->getSize($this, $name);
        benchmark("{$this->server}::size", $this->buildAddress($name));

        return $size;
    }

    /**
     * Upload storage object using given filename or stream. Method can return false in case of failed
     * upload or thrown custom exception if needed.
     *
     * @param string                                                     $name   Given storage object
     *                                                                           name.
     * @param string|StreamInterface|UploadedFileInterface|StorageObject $origin Local filename or
     *                                                                           stream to use for
     *                                                                           creation.
     * @return StorageObject
     */
    public function put($name, $origin)
    {
        $this->log("Uploading to '{$this->buildAddress($name)}' at '{$this->server}' server.");

        if ($origin instanceof UploadedFileInterface || $origin instanceof StorageObject)
        {
            //Known simplification for UploadedFile
            $origin = $origin->getStream();
        }

        if (is_resource($origin))
        {
            $origin = new Stream($origin);
        }

        benchmark("{$this->server}::upload", $this->buildAddress($name));

        if (!$this->getServer()->put($this, $name, $origin))
        {
            throw new StorageException(
                "Unable to upload content into '{$this->buildAddress($name)}' at '{$this->server}' server."
            );
        }

        benchmark("{$this->server}::upload", $this->buildAddress($name));

        return new StorageObject($this->buildAddress($name), $name, $this->storage, $this);
    }

    /**
     * Allocate local filename for remote storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. File is in readonly
     * mode, and in some cases will be erased on shutdown.
     *
     * @param string $name Storage object name.
     * @return string
     * @throws StorageException
     */
    public function allocateFilename($name)
    {
        $this->log("Getting local filename of '{$this->buildAddress($name)}' at '{$this->server}' server.");

        benchmark("{$this->server}::filename", $this->buildAddress($name));
        if (!$filename = $this->getServer()->allocateFilename($this, $name))
        {
            throw new StorageException(
                "Unable to allocate local filename for '{$this->buildAddress($name)}' "
                . "at '{$this->server}' server."
            );
        }
        benchmark("{$this->server}::filename", $this->buildAddress($name));

        return $filename;
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very similar
     * to localFilename, however in some cases it may store data content in memory.
     *
     * @param string $name Storage object name.
     * @return StreamInterface
     * @throws StorageException
     */
    public function getStream($name)
    {
        $this->log("Getting stream for '{$this->buildAddress($name)}' at '{$this->server}' server.");

        benchmark("{$this->server}::stream", $this->buildAddress($name));
        if (!$stream = $this->getServer()->getStream($this, $name))
        {
            throw new StorageException(
                "Unable to allocate stream for '{$this->buildAddress($name)}' at '{$this->server}' server."
            );
        }
        benchmark("{$this->server}::stream", $this->buildAddress($name));

        return $stream;
    }

    /**
     * Rename storage object without changing it's container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * @param string $oldname Storage object name.
     * @param string $newname New storage object name.
     * @return bool
     * @throws StorageException
     */
    public function rename($oldname, $newname)
    {
        if ($oldname == $newname)
        {
            return true;
        }

        $this->log(
            "Renaming '{$this->buildAddress($oldname)}' to '{$this->buildAddress($newname)}' "
            . "at '{$this->server}' server."
        );

        benchmark("{$this->server}::rename", $this->buildAddress($oldname));
        if (!$this->getServer()->rename($this, $oldname, $newname))
        {
            throw new StorageException(
                "Unable to rename '{$this->buildAddress($oldname)}' "
                . "to '{$this->buildAddress($newname)}' at '{$this->server}' server."
            );
        }
        benchmark("{$this->server}::rename", $this->buildAddress($oldname));

        return $this->buildAddress($newname);
    }

    /**
     * Delete storage object from specified container. Method should not fail if object does not
     * exists.
     *
     * @param string $name Storage object name.
     */
    public function delete($name)
    {
        $this->log("Delete '{$this->buildAddress($name)}' at '{$this->server}' server.");

        benchmark("{$this->server}::delete", $this->buildAddress($name));
        $this->getServer()->delete($this, $name);
        benchmark("{$this->server}::delete", $this->buildAddress($name));
    }

    /**
     * Copy object to another internal (under same server) container, this operation may not
     * require file download and can be performed remotely.
     *
     * Method will return new instance of StorageObject associated with copied data.
     *
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Storage object name.
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
            $this->log(
                "Internal copying of '{$this->buildAddress($name)}' "
                . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
            );

            benchmark("{$this->server}::copy", $this->buildAddress($name));
            if (!$this->getServer()->copy($this, $destination, $name))
            {
                throw new StorageException(
                    "Unable to copy '{$this->buildAddress($name)}' "
                    . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
                );
            }
            benchmark("{$this->server}::copy", $this->buildAddress($name));
        }
        else
        {
            $this->log(
                "External copying of '{$this->server}'.'{$this->buildAddress($name)}' "
                . "to '{$destination->server}'.'{$destination->buildAddress($name)}'."
            );

            $stream = $this->getStream($name);

            //Now we will try to copy object using current server/memory as a buffer.
            if (empty($stream) || !$destination->put($name, $stream))
            {
                throw new StorageException(
                    "Unable to copy '{$this->server}'.'{$this->buildAddress($name)}' "
                    . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
                );
            }
        }

        return new StorageObject($destination->buildAddress($name), $name, $this->storage, $destination);
    }

    /**
     * Replace object to another internal (under same server) container, this operation may not
     * require file download and can be performed remotely.
     *
     * Method will return replaced storage object address.
     *
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Storage object name.
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
            $this->log(
                "Internal moving '{$this->buildAddress($name)}' "
                . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
            );

            benchmark("{$this->server}::replace", $this->buildAddress($name));
            if (!$this->getServer()->replace($this, $destination, $name))
            {
                throw new StorageException(
                    "Unable to move '{$this->buildAddress($name)}' "
                    . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
                );
            }
            benchmark("{$this->server}::replace", $this->buildAddress($name));
        }
        else
        {
            $this->log(
                "External moving '{$this->server}'.'{$this->buildAddress($name)}'"
                . " to '{$destination->server}'.'{$destination->buildAddress($name)}'."
            );

            $stream = $this->getStream($name);

            //Now we will try to replace object using current server/memory as a buffer.
            if (empty($stream) || !$destination->put($name, $stream))
            {
                throw new StorageException(
                    "Unable to replace '{$this->server}'.'{$this->buildAddress($name)}' "
                    . "to '{$destination->buildAddress($name)}' at '{$this->server}' server."
                );
            }

            $stream->detach() && $this->delete($name);
        }

        return $destination->buildAddress($name);
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