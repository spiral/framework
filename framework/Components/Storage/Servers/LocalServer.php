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
use Spiral\Components\Http\Stream;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageManager;
use Spiral\Components\Storage\StorageStorageServer;

class LocalServer extends StorageStorageServer
{
    /**
     * File component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based.
     * Every adapter should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param array          $options Storage connection options.
     * @param StorageManager $storage StorageManager component.
     * @param FileManager    $file    FileManager component.
     */
    public function __construct(array $options, StorageManager $storage, FileManager $file)
    {
        $this->file = $file;
    }

    /**
     * Check if given object (name) exists in specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return bool
     */
    public function exists(StorageContainer $container, $name)
    {
        return $this->file->exists($container->options['folder'] . $name);
    }

    /**
     * Retrieve object size in bytes, should return 0 if object not exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return int
     */
    public function size(StorageContainer $container, $name)
    {
        return $this->file->exists($container->options['folder'] . $name)
            ? $this->file->size($container->options['folder'] . $name)
            : 0;
    }

    /**
     * Create new storage object using given filename. File will be replaced to new location and will
     * not available using old filename.
     *
     * @param string           $filename  Local filename to use for creation.
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return bool
     */
    public function create($filename, StorageContainer $container, $name)
    {
        if (!empty($filename) && $this->file->exists($filename))
        {
            return $this->internalCopy($container, $filename, $container->options['folder'] . $name);
        }

        if ($this->file->touch($filename = $container->options['folder'] . $name))
        {
            $mode = !empty($container->options['mode']) ?: FileManager::RUNTIME;

            return $this->file->setPermissions($filename, $mode);
        }

        return false;
    }

    /**
     * Allocate local filename for remote storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. All object stored in
     * temporary files should be registered in File::$removeFiles, to be removed after script ends to
     * clean used hard drive space.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return string|bool
     */
    public function localFilename(StorageContainer $container, $name)
    {
        return $this->file->exists($container->options['folder'] . $name)
            ? $container->options['folder'] . $name
            : false;
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very identical
     * to localFilename, however in some cases it may store data content in memory simplifying
     * development.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return StreamInterface|bool
     */
    public function getStream(StorageContainer $container, $name)
    {
        if (!$this->exists($container, $name))
        {
            return false;
        }

        return new Stream($this->localFilename($container, $name));
    }

    /**
     * Remove storage object without changing it's own container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @param string           $newName   New object name.
     * @return bool
     */
    public function rename(StorageContainer $container, $name, $newName)
    {
        return $this->internalMove(
            $container,
            $container->options['folder'] . $name,
            $container->options['folder'] . $newName
        );
    }

    /**
     * Delete storage object from specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     */
    public function delete(StorageContainer $container, $name)
    {
        $this->file->remove($container->options['folder'] . $name);
    }

    /**
     * Copy object to another internal (under same server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
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
     * Move object to another internal (under same server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
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

        $mode = !empty($container->options['mode']) ?: FileManager::RUNTIME;
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

        $mode = !empty($container->options['mode']) ?: FileManager::RUNTIME;
        $this->file->ensureDirectory(dirname($destination), $mode);

        if (!$this->file->copy($filename, $destination))
        {
            return false;
        }

        return $this->file->setPermissions($destination, $mode);
    }
}