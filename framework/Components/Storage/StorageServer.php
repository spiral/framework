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
use Spiral\Components\Files\StreamWrapper;

abstract class StorageServer implements StorageServerInterface
{
    /**
     * List of known file mimetypes to generate valid file Content-Type header.
     *
     * @var array
     */
    protected $mimetypes = array(
        'default' => 'application/octet-stream',
        'jpg'     => 'image/jpeg',
        'jpeg'    => 'image/jpeg',
        'gif'     => 'image/gif',
        'png'     => 'image/png',
        'tif'     => 'image/tiff',
        'tiff'    => 'image/tiff',
        'ico'     => 'image/x-icon',
        'swf'     => 'application/x-shockwave-flash',
        'pdf'     => 'application/pdf',
        'zip'     => 'application/zip',
        'gz'      => 'application/x-gzip',
        'tar'     => 'application/x-tar',
        'bz'      => 'application/x-bzip',
        'bz2'     => 'application/x-bzip2',
        'txt'     => 'text/plain',
        'htm'     => 'text/html',
        'html'    => 'text/html',
        'css'     => 'text/css',
        'js'      => 'text/javascript',
        'xml'     => 'text/xml',
        'ogg'     => 'application/ogg',
        'mp3'     => 'audio/mpeg',
        'wav'     => 'audio/x-wav',
        'avi'     => 'video/x-msvideo',
        'mpg'     => 'video/mpeg',
        'mpeg'    => 'video/mpeg',
        'mov'     => 'video/quicktime',
        'flv'     => 'video/x-flv',
        'php'     => 'text/x-php'
    );

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
        //Default implementation will use stream to create temporary filename, such filename
        //can't be used outside php scope
        return StreamWrapper::getUri($this->getStream($container, $name));
    }

    /**
     * Find appropriate file mimetype by given filename (extension will be used).
     *
     * @param string $filename Local filename.
     * @return string
     */
    protected function getMimeType($filename)
    {
        //File extension
        $extension = strtolower(pathinfo($filename, 4));

        if (isset($this->mimetypes[$extension]))
        {
            return $this->mimetypes[$extension];
        }

        return $this->mimetypes['default'];
    }

    /**
     * Get filename to be used in file based methods and etc. Will create virtual Uri for streams.
     *
     * @param string|StreamInterface $filename
     * @return string
     */
    protected function resolveFilename($filename)
    {
        if (empty($filename) || is_string($filename))
        {
            return $filename;
        }

        if ($filename instanceof StreamInterface)
        {
            return StreamWrapper::getUri($filename);
        }

        throw new StorageException("Unable to get filename for non Stream instance.");
    }
} 