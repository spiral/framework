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
use Spiral\Components\Files\FileManager;

interface StorageServerInterface
{
    /**
     * Every server represent one virtual storage which can be either local, remote or cloud based.
     * Every server should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param FileManager $file    File component.
     * @param array       $options Storage connection options.
     */
    public function __construct(FileManager $file, array $options);

    /**
     * Check if given object (name) exists in specified container. Method should never fail if file
     * not exists and will return bool in any condition.
     *
     * @param StorageContainer $container Container instance associated with specific server.
     * @param string           $name      Storage object name.
     * @return bool
     */
    public function isExists(StorageContainer $container, $name);

    /**
     * Retrieve object size in bytes, should return false if object does not exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return int|bool
     */
    public function getSize(StorageContainer $container, $name);

    /**
     * Upload storage object using given filename or stream. Method can return false in case of failed
     * upload or thrown custom exception if needed.
     *
     * @param StorageContainer       $container Container instance.
     * @param string                 $name      Given storage object name.
     * @param string|StreamInterface $origin    Local filename or stream to use for creation.
     * @return bool
     */
    public function upload(StorageContainer $container, $name, $origin);

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
    public function allocateFilename(StorageContainer $container, $name);

    /**
     * Get temporary read-only stream used to represent remote content. This method is very similar
     * to localFilename, however in some cases it may store data content in memory.
     *
     * Method should return false or thrown an exception if stream can not be allocated.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return StreamInterface|null
     */
    public function getStream(StorageContainer $container, $name);

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
    public function rename(StorageContainer $container, $oldname, $newname);

    /**
     * Delete storage object from specified container. Method should not fail if object does not
     * exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     */
    public function delete(StorageContainer $container, $name);

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
    public function copy(StorageContainer $container, StorageContainer $destination, $name);

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
    public function replace(StorageContainer $container, StorageContainer $destination, $name);
}