<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers\Local;

use Spiral\Components\Storage\ServerInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageManager;

class LocalServer implements ServerInterface
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
     * Move helper, ensure target directory existence, file permissions and etc.
     *
     * @param StorageContainer $container   Destination container.
     * @param string           $filename    Original filename.
     * @param string           $destination Destination filename.
     * @return bool
     */
    protected function moveHelper(StorageContainer $container, $filename, $destination)
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
    protected function copyHelper(StorageContainer $container, $filename, $destination)
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
    public function filesize(StorageContainer $container, $name)
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
        if ($filename && $this->file->exists($filename))
        {
            return $this->moveHelper($container, $filename, $container->options['folder'] . $name);
        }

        if ($this->file->touch($filename = $container->options['folder'] . $name))
        {
            $mode = !empty($container->options['mode']) ?: FileManager::RUNTIME;

            return $this->file->setPermissions($filename, $mode);
        }

        return false;
    }

    /**
     * Allocate local filename for remove storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. All object stored in
     * temporary files should be registered in File::$removeFiles, to be removed after script ends to
     * clean used hard drive space.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return string
     */
    public function localFilename(StorageContainer $container, $name)
    {
        return $this->file->exists($container->options['folder'] . $name)
            ? $container->options['folder'] . $name
            : false;
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
        return $this->moveHelper(
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
     * Copy object to another internal (under save server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
     * @return bool
     */
    public function copy(StorageContainer $container, StorageContainer $destination, $name)
    {
        return $this->copyHelper(
            $destination,
            $container->options['folder'] . $name,
            $destination->options['folder'] . $name
        );
    }

    /**
     * Move object to another internal (under save server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
     * @return bool
     */
    public function replace(StorageContainer $container, StorageContainer $destination, $name)
    {
        return $this->moveHelper(
            $destination,
            $container->options['folder'] . $name,
            $destination->options['folder'] . $name
        );
    }
}