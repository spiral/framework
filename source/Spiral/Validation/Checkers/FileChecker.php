<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation\Checkers;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Files\FilesInterface;
use Spiral\Validation\Checkers\Traits\FileTrait;
use Spiral\Validation\Prototypes\AbstractChecker;

class FileChecker extends AbstractChecker implements SingletonInterface
{
    use FileTrait;

    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'exists'    => '[[File does not exists.]]',
        'uploaded'  => '[[File not received, please try again.]]',
        'size'      => '[[File exceeds the maximum file size of {1}KB.]]',
        'extension' => '[[File has an invalid file format.]]',
    ];

    /**
     * @param FilesInterface     $files
     * @param ContainerInterface $container
     */
    public function __construct(FilesInterface $files, ContainerInterface $container = null)
    {
        $this->files = $files;
        parent::__construct($container);
    }

    /**
     * Check if file exist.
     *
     * @param mixed $file
     *
     * @return bool
     */
    public function exists($file): bool
    {
        return !empty($this->resolveFilename($file));
    }

    /**
     * Check if file been uploaded.
     *
     * @param mixed $file Local file or uploaded file array.
     *
     * @return bool
     */
    public function uploaded($file): bool
    {
        return $this->isUploaded($file);
    }

    /**
     * Check if file size less that specified value in KB.
     *
     * @todo add Pow multiplier
     *
     * @param mixed $file Local file or uploaded file array.
     * @param int   $size Size in KBytes.
     *
     * @return bool
     */
    public function size($file, int $size): bool
    {
        if (empty($filename = $this->resolveFilename($file))) {
            return false;
        }

        return $this->files->size($filename) <= ($size * 1024);
    }

    /**
     * Check if file extension in whitelist. Client name of uploaded file will be used!
     * It is recommended to use external validation like media type based on file mimetype or
     * ensure that resource is properly converted.
     *
     * @param mixed        $file
     * @param array|string $extensions
     *
     * @return bool
     */
    public function extension($file, $extensions): bool
    {
        if (!is_array($extensions)) {
            $extensions = array_slice(func_get_args(), 1);
        }

        if ($file instanceof UploadedFileInterface) {
            return in_array(
                $this->files->extension($file->getClientFilename()),
                $extensions
            );
        }

        return in_array($this->files->extension($file), $extensions);
    }
}
