<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */

namespace Spiral\Components\Storage;

use Psr\Http\Message\StreamInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Files\StreamWrapper;
use Spiral\Components\Http\Stream;

abstract class StorageServer implements StorageServerInterface
{
    /**
     * Default mimetype to be used when nothing else can be applied.
     */
    const DEFAULT_MIMETYPE = 'application/octet-stream';

    /**
     * Server configuration, connection options, auth keys and certificates.
     *
     * @var array
     */
    protected $options = array();

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
     * @param FileManager $file    FileManager component.
     * @param array       $options Storage connection options.
     */
    public function __construct(FileManager $file, array $options)
    {
        $this->options = $options + $this->options;
        $this->file = $file;
    }

    /**
     * Allocate local filename for remove storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. File is in readonly
     * mode, and in some cases will be erased on shutdown.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return string|bool
     */
    public function allocateFilename(StorageContainer $container, $name)
    {
        if (empty($stream = $this->getStream($container, $name)))
        {
            return false;
        }

        //Default implementation will use stream to create temporary filename, such filename
        //can't be used outside php scope
        return StreamWrapper::getUri($stream);
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
        return $this->upload($destination, $name, $this->getStream($container, $name));
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
        if ($this->copy($container, $destination, $name))
        {
            $this->delete($container, $name);

            return true;
        }

        return false;
    }

    /**
     * Get filename to be used in file based methods and etc. Will create virtual Uri for streams.
     *
     * @param string|StreamInterface $origin
     * @return string
     */
    protected function castFilename($origin)
    {
        if (empty($origin) || is_string($origin))
        {
            if (!$this->file->exists($origin))
            {
                return StreamWrapper::getUri(\GuzzleHttp\Psr7\stream_for(''));
            }

            return $origin;
        }

        if ($origin instanceof StreamInterface)
        {
            return StreamWrapper::getUri($origin);
        }

        throw new StorageException("Unable to get filename for non Stream instance.");
    }

    /**
     * Get stream associated with origin data.
     *
     * @param string|StreamInterface $origin
     * @return StreamInterface
     */
    protected function castStream($origin)
    {
        if ($origin instanceof StreamInterface)
        {
            return $origin;
        }

        if (empty($origin))
        {
            return \GuzzleHttp\Psr7\stream_for('');
        }

        return new Stream($origin);
    }
} 