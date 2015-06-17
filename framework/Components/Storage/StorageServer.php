<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */

namespace Spiral\Components\Storage;

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
     * Find appropriate file mimetype by given filename.
     *
     * @param string $filename Local filename.
     * @return string
     */
    protected function getMimetype($filename)
    {
        //File extension
        $extension = strtolower(pathinfo($filename, 4));

        if (isset($this->mimetypes[$extension]))
        {
            return $this->mimetypes[$extension];
        }

        return $this->mimetypes['default'];
    }
} 