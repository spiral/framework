<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers\Amazon;

use Spiral\Components\Storage\ServerInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Storage\Servers\Traits\MimetypeTrait;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageException;
use Spiral\Components\Storage\StorageManager;

class AmazonCloudServer implements ServerInterface
{
    /**
     * Common storage server functionality.
     */
    use MimetypeTrait;

    /**
     * Storage component.
     *
     * @invisible
     * @var StorageManager
     */
    protected $storage = null;

    /**
     * File component.
     *
     * @invisible
     * @var FileManager
     */
    protected $file = null;

    /**
     * Server configuration, connection options, auth keys and certificates.
     *
     * @var array
     */
    protected $options = array(
        'server'      => 's3.amazonaws.com',
        'secured'     => true,
        'certificate' => '',
        'accessKey'   => '',
        'secretKey'   => '',
    );

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based.
     * Every adapter should support basic set of low-level operations (create, move, copy and etc).
     *
     * @todo Remove all legacy code and start using Amazon SDK.
     * @param array          $options Storage connection options.
     * @param StorageManager $storage StorageManager component.
     * @param FileManager    $file    FileManager component.
     * @throws StorageException
     */
    public function __construct(array $options, StorageManager $storage, FileManager $file)
    {
        $this->options = $options + $this->options;
        $this->storage = $storage;
        $this->file = $file;

        if (!extension_loaded('hash'))
        {
            throw new StorageException(
                "Unable to initialize Amazon storage adapter, extension 'hash' not found."
            );
        }
    }

    /**
     * Amazon query helper used to perform requests to S3 storage servers.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @param string           $method    HTTP method.
     * @return AmazonQuery
     */
    protected function query(StorageContainer $container, $name, $method = 'HEAD')
    {
        return new AmazonQuery($this->options, $container, $name, $method);
    }

    /**
     * Check if given object (name) exists in specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @param array            $headers   Headers associated with file.
     * @return bool
     */
    public function exists(StorageContainer $container, $name, &$headers = null)
    {
        $headers = $this->query($container, $name)->run();

        return $headers['status'] == 200;
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
        if (!$this->exists($container, $name, $headers))
        {
            return 0;
        }

        return (int)$headers['content-length'];
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
        if (!$this->file->exists($filename))
        {
            $filename = $this->file->tempFilename();
        }

        if (($mimetype = $this->getMimetype($filename)) == $this->mimetypes['default'])
        {
            $mimetype = $this->getMimetype($name);
        }

        $result = $this->query($container, $name, 'PUT')
            ->command('acl', $container->options['public'] ? 'public-read' : 'private')
            ->command('Content-Type', $mimetype)
            ->filename($filename)
            ->setHeader('Content-MD5', base64_encode(md5_file($filename, true)))
            ->setHeader('Content-Type', $mimetype)
            ->run();

        return $result['status'] == 200 || $result['status'] == 201;
    }

    /**
     * Allocate local filename for remove storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. All object stored in
     * temporary files should be registered in File::$removeFiles, to be removed after script ends
     * to clean used hard drive space.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return string
     */
    public function localFilename(StorageContainer $container, $name)
    {
        //File should be removed after processing
        $this->file->blackspot($filename = $this->file->tempFilename($this->file->extension($name)));
        $result = $this->query($container, $name, 'GET')->filename($filename)->run();

        return $result['status'] == 200 ? $filename : false;
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
        if ($newName == $name)
        {
            return true;
        }

        $result = $this->query($container, $newName, 'PUT')
            ->command('copy-source', '/' . $container->options['bucket'] . '/' . rawurlencode($name))
            ->command('acl', $container->options['public'] ? 'public-read' : 'private')
            ->run();

        if ($result['status'] == 200)
        {
            $this->delete($container, $name);

            return true;
        }

        return false;
    }

    /**
     * Delete storage object from specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     */
    public function delete(StorageContainer $container, $name)
    {
        $this->query($container, $name, 'DELETE')->run();
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
    public function copy(StorageContainer $container, StorageContainer $destination, $name)
    {
        if ($container->options['bucket'] == $destination->options['bucket'])
        {
            return true;
        }

        $result = $this->query($destination, $name, 'PUT')
            ->command('copy-source', '/' . $container->options['bucket'] . '/' . rawurlencode($name))
            ->command('acl', $destination->options['public'] ? 'public-read' : 'private')
            ->run();

        return $result['status'] == 200 || $result['status'] == 201;
    }

    /**
     * Move object to another internal (under save server) container, this operation
     * should may not require file download and can be performed remotely.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
     * @return bool
     */
    public function replace(StorageContainer $container, StorageContainer $destination, $name)
    {
        if ($container->options['bucket'] == $destination->options['bucket'])
        {
            return false;
        }

        $result = $this->query($destination, $name, 'PUT')
            ->command('copy-source', '/' . $container->options['bucket'] . '/' . rawurlencode($name))
            ->command('acl', $destination->options['public'] ? 'public-read' : 'private')
            ->run();

        if ($result['status'] == 200)
        {
            $this->delete($container, $name);

            return true;
        }

        return false;
    }
}