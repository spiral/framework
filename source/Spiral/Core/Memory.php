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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
    public function loadData(string $section, string $location = null, string &$filename = null)
    {
        $filename = $this->memoryFilename($section, $location);

        if (!file_exists($filename)) {
            return null;
        }

        try {
            return include($filename);
        } catch (\ErrorException $exception) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveData(string $section, $data, string $location = null)
    {
        $filename = $this->memoryFilename($section, $location);

        //We are packing data into plain php
        $data = '<?php return ' . var_export($data, true) . ';';

        //We need help to write file with directory creation
        $this->files->write($filename, $data, FilesInterface::RUNTIME, true);
    }

    /**
     * Get all memory sections belongs to given memory location (default location to be used if
     * none specified).
     *
     * @param string $location
     *
     * @return array
     */
    public function getSections(string $location = null)
    {
        if (!empty($location)) {
            $location = $this->directory . $location . '/';
        } else {
            $location = $this->directory;
        }

        if (!$this->files->exists($location)) {
            return [];
        }

        $finder = new Finder();
        $finder->in($location);

        /**
         * @var SplFileInfo $file
         */
        $sections = [];
        foreach ($finder->name("*" . static::EXTENSION) as $file) {
            $sections[] = substr($file->getRelativePathname(), 0, -1 * (strlen(static::EXTENSION)));
        }

        return $sections;
    }

    /**
     * Get extension to use for runtime data or configuration cache.
     *
     * @param string $name     Runtime data file name (without extension).
     * @param string $location Location to store data in.
     *
     * @return string
     */
    private function memoryFilename(string $name, string $location = null): string
    {
        $name = strtolower(str_replace(['/', '\\'], '-', $name));

        if (!empty($location)) {
            $location = $this->directory . $location . '/';
        } else {
            $location = $this->directory;
        }

        //Runtime cache
        return $location . $name . static::EXTENSION;
    }
}
