<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers;

use Psr\Http\Message\StreamInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Http\Stream;
use Spiral\Components\ODM\MongoDatabase;
use Spiral\Components\ODM\ODM;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageServer;

class GridfsServer extends StorageServer
{
    /**
     * Server configuration, connection options, auth keys and certificates.
     *
     * @var array
     */
    protected $options = array(
        'database' => 'default'
    );

    /**
     * Associated mongo database.
     *
     * @var MongoDatabase
     */
    protected $database = null;

    /**
     * Every server represent one virtual storage which can be either local, remote or cloud based.
     * Every server should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param FileManager $file    File component.
     * @param array       $options Storage connection options.
     * @param ODM         $odm     ODM manager is required to resolve MongoDatabase.
     */
    public function __construct(FileManager $file, array $options, ODM $odm = null)
    {
        parent::__construct($file, $options);
        $odm = $odm ?: ODM::getInstance();

        $this->database = $odm->db($this->options['database']);
    }

    /**
     * Check if given object (name) exists in specified container. Method should never fail if file
     * not exists and will return bool in any condition.
     *
     * @param StorageContainer $container Container instance associated with specific server.
     * @param string           $name      Storage object name.
     * @return bool|\MongoGridFSFile
     */
    public function exists(StorageContainer $container, $name)
    {
        return $this->getGridFS($container)->findOne(array(
            'filename' => $name
        ));
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
        if (!$file = $this->exists($container, $name))
        {
            return false;
        }

        return $file->getSize();
    }

    /**
     * Upload storage object using given filename or stream. Method can return false in case of failed
     * upload or thrown custom exception if needed.
     *
     * @link https://github.com/mongodb/mongo-php-driver/blob/master/gridfs/gridfs.c#L690
     * @link https://github.com/mongodb/mongo-php-driver/blob/master/gridfs/gridfs.c#L241
     * @param StorageContainer       $container Container instance.
     * @param string                 $name      Given storage object name.
     * @param string|StreamInterface $origin    Local filename or stream to use for creation.
     * @return bool
     */
    public function put(StorageContainer $container, $name, $origin)
    {
        //We have to remove existed file first, this might not be super optimal operation.
        //Can be re-thinked
        $this->delete($container, $name);

        /**
         * For some reason mongo driver i have locally don't want to read wrapped streams,
         * it either dies with "error setting up file" or hangs.
         *
         * I wasn't able to debug cause of this error at this moment as i don't have Visual Studio
         * at this PC.
         *
         * However, error caused by some code from this file. In a meantime i will write content to
         * local file before sending it to mongo, this is DIRTY, but will work for some time.
         *
         * @link https://github.com/mongodb/mongo-php-driver/blob/master/gridfs/gridfs.c
         */

        $tempFilename = $this->file->tempFilename();
        copy($this->castFilename($origin), $tempFilename);

        if (!$this->getGridFS($container)->storeFile($tempFilename, array('filename' => $name)))
        {
            return false;
        }

        $this->file->delete($tempFilename);

        return true;
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
        if (!$file = $this->exists($container, $name))
        {
            return false;
        }

        return new Stream($file->getResource());
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
        $this->delete($container, $newname);

        return $this->getGridFS($container)->update(
            array(
                'filename' => $oldname
            ),
            array(
                '$set' => array('filename' => $newname)
            )
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
        $this->getGridFS($container)->remove(array('filename' => $name));
    }

    /**
     * Get valid gridfs collection associated with container.
     *
     * @param StorageContainer $container
     * @return \MongoGridFS
     */
    protected function getGridFS(StorageContainer $container)
    {
        $gridFs = $this->database->getGridFS($container->options['collection']);
        $gridFs->ensureIndex(array('filename' => 1));

        return $gridFs;
    }
}