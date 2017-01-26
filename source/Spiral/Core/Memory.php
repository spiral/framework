<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Core;

use Spiral\Core\Exceptions\ScopeException;
use Spiral\Files\FilesInterface;

/**
 * Default implementation of MemoryInterface.
 */
class Memory implements MemoryInterface
{
    /**
     * Extension for memory files.
     */
    const EXTENSION = '.php';

    /**
     * Default memory location.
     *
     * @var string
     */
    private $directory = null;

    /**
     * Files are needed for write/read operations.
     *
     * @var FilesInterface
     */
    private $files = null;

    /**
     * @param string         $directory
     * @param FilesInterface $files
     *
     * @throws ScopeException
     */
    public function __construct(string $directory, FilesInterface $files)
    {
        $this->directory = $directory;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $filename Cache filename.
     */
    public function loadData(string $section, string &$filename = null)
    {
        $filename = $this->memoryFilename($section);

        if (!file_exists($filename)) {
            return null;
        }

        try {
            return include($filename);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveData(string $section, $data)
    {
        $filename = $this->memoryFilename($section);

        //We are packing data into plain php
        $data = '<?php return ' . var_export($data, true) . ';';

        //We need help to write file with directory creation
        $this->files->write($filename, $data, FilesInterface::RUNTIME, true);
    }

    /**
     * Get extension to use for runtime data or configuration cache.
     *
     * @param string $name Runtime data file name (without extension).
     *
     * @return string
     */
    private function memoryFilename(string $name): string
    {
        $name = strtolower(str_replace(['/', '\\'], '-', $name));

        //Runtime cache
        return $this->directory . $name . static::EXTENSION;
    }
}
