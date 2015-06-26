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
        $this->file = $file;
    }

    /**
     * Allocate local filename for remote storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. All object stored in
     * temporary files should be registered in FileManager->blackspot(), to be removed after script
     * ends to clean used hard drive space.
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
     * Get filename to be used in file based methods and etc. Will create virtual Uri for streams.
     *
     * @param string|StreamInterface $origin
     * @return string
     */
    protected function resolveFilename($origin)
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
    protected function resolveStream($origin)
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