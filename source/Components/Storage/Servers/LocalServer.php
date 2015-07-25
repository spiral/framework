<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */
namespace Spiral\Components\Storage\Servers;

use Psr\Http\Message\StreamInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageServer;

class LocalServer extends StorageServer
{
    /**
     * Check if given object (name) exists in specified container. Method should never fail if file
     * not exists and will return bool in any condition.
     *
     * @param StorageContainer $container Container instance associated with specific server.
     * @param string           $name      Storage object name.
     * @return bool
     */
    public function exists(StorageContainer $container, $name)
    {
        return $this->file->exists($container->options['folder'] . $name);
    }

    /**
     * Retrieve object size in bytes, should return false if object does not exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return int|bool
     */
    public function getSize(StorageContainer $container, $name)
    {
        return $this->file->exists($container->options['folder'] . $name)
            ? $this->file->size($container->options['folder'] . $name)
            : false;
    }

    /**
     * Upload storage object using given filename or stream. Method can return false in case of failed
     * upload or thrown custom exception if needed.
     *
     * @param StorageContainer       $container Container instance.
     * @param string                 $name      Given storage object name.
     * @param string|StreamInterface $origin    Local filename or stream to use for creation.
     * @return bool
     */
    public function put(StorageContainer $container, $name, $origin)
    {
        return $this->internalCopy(
            $container,
            $this->castFilename($origin),
            $container->options['folder'] . $name
        );
    }

    /**
     * Allocate local filename for remote storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. File is in readonly
     * mode, and in some cases will be erased on shutdown.
     *
     * Method should return false or thrown an exception if local filename can not be allocated.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return string|bool
     */
    public function allocateFilename(StorageContainer $container, $name)
    {
        return $this->file->exists($container->options['folder'] . $name)
            ? $container->options['folder'] . $name
            : false;
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very similar
     * to localFilename, however in some cases it may store data content in memory.
     *
     * Method should return false or thrown an exception if stream can not be allocated.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return StreamInterface|false
     */
    public function getStream(StorageContainer $container, $name)
    {
        if (!$this->exists($container, $name))
        {
            return false;
        }

        //Getting readonly stream
        return \GuzzleHttp\Psr7\stream_for(fopen($this->allocateFilename($container, $name), 'rb'));
    }

    /**
     * Rename storage object without changing it's container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * Method should return false or thrown an exception if object can not be renamed.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $oldname   Storage object name.
     * @param string           $newname   New storage object name.
     * @return bool
     */
    public function rename(StorageContainer $container, $oldname, $newname)
    {
        return $this->internalMove(
            $container,
            $container->options['folder'] . $oldname,
            $container->options['folder'] . $newname
        );
    }

    /**
     * Delete storage object from specified container. Method should not fail if object does not
     * exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     */
    public function delete(StorageContainer $container, $name)
    {
        $this->file->delete($container->options['folder'] . $name);
    }

    /**
     * Copy object to another internal (under same server) container, this operation may not
     * require file download and can be performed remotely.
     *
     * Method should return false or thrown an exception if object can not be copied.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Storage object name.
     * @return bool
     */
    public function copy(StorageContainer $container, StorageContainer $destination, $name)
    {
        return $this->internalCopy(
            $destination,
            $container->options['folder'] . $name,
            $destination->options['folder'] . $name
        );
    }

    /**
     * Replace object to another internal (under same server) container, this operation may not
     * require file download and can be performed remotely.
     *
     * Method should return false or thrown an exception if object can not be replaced.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Storage object name.
     * @return bool
     */
    public function replace(StorageContainer $container, StorageContainer $destination, $name)
    {
        return $this->internalMove(
            $destination,
            $container->options['folder'] . $name,
            $destination->options['folder'] . $name
        );
    }

    /**
     * Move helper, ensure target directory existence, file permissions and etc.
     *
     * @param StorageContainer $container   Destination container.
     * @param string           $filename    Original filename.
     * @param string           $destination Destination filename.
     * @return bool
     */
    protected function internalMove(StorageContainer $container, $filename, $destination)
    {
        if (!$this->file->exists($filename))
        {
            return false;
        }

        $mode = !empty($container->options['mode']) ? $container->options['mode'] : FileManager::RUNTIME;
        $this->file->ensureDirectory(dirname($destination), $mode);

        if (!$this->file->move($filename, $destination))
        {
            return false;
        }

        return $this->file->setPermissions($destination, $mode);
    }

    /**
     * Copy helper, ensure target directory existence, file permissions and etc.
     *
     * @param StorageContainer $container   Destination container.
     * @param string           $filename    Original filename.
     * @param string           $destination Destination filename.
     * @return bool
     */
    protected function internalCopy(StorageContainer $container, $filename, $destination)
    {
        if (!$this->file->exists($filename))
        {
            return false;
        }

        $mode = !empty($container->options['mode']) ? $container->options['mode'] : FileManager::RUNTIME;
        $this->file->ensureDirectory(dirname($destination), $mode);

        if (!$this->file->copy($filename, $destination))
        {
            return false;
        }

        return $this->file->setPermissions($destination, $mode);
    }
}