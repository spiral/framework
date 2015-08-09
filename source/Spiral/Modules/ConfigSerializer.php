<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules;

use Spiral\Core\Core;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\ArraySerializer;

/**
 * ConfigSerializer used to create nice looking configuration files based on data provided by
 * ConfigWriter. ConfigSerializer will directory() method into paths.
 */
class ConfigSerializer extends ArraySerializer
{
    /**
     * @invisible
     * @var Core
     */
    protected $core = null;

    /**
     * @invisible
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param Core           $core
     * @param FilesInterface $files
     */
    public function __construct(Core $core, FilesInterface $files)
    {
        $this->core = $core;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    protected function packValue($name, $value)
    {
        if (!is_string($value)) {
            return parent::packValue($name, $value);
        }

        $alias = $directory = $hasAlias = false;
        foreach ($this->getDirectories() as $alias => $directory) {
            if (strpos($this->files->normalizePath($value), $directory) === 0) {
                //We found directory alias
                $hasAlias = true;
                break;
            }
        }

        if (!$hasAlias) {
            $value = var_export($value, true);

            //Removing slashes in namespace separators (we can do it due second char is uppercase)
            $value = preg_replace('/\\\\([A-Z])/', '\\$1', $value);

            return $name . $value;
        };

        //Trimming directory
        $value = substr($value, strlen($directory));

        //Directory alias
        $value = 'directory("' . $alias . '") . ' . var_export($value, true);

        return $name . $value;
    }

    /**
     * List of core directories with normalized paths, longest directories first.
     *
     * @return array
     */
    private function getDirectories()
    {
        $directories = $this->core->getDirectories();

        foreach ($directories as &$directory) {
            $directory = $this->files->normalizePath($directory);
            unset($directory);
        }

        //Sorting to get longest first
        uasort($directories, function ($valueA, $valueB) {
            return strlen($valueA) < strlen($valueB);
        });

        return $directories;
    }
}