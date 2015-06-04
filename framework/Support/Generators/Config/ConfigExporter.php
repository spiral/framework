<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators\Config;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Core;
use Spiral\Support\Generators\ArrayExporter;

class ConfigExporter extends ArrayExporter
{
    /**
     * Core instance.
     *
     * @var Core
     */
    protected $core = null;

    /**
     * FileManager.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * ArrayExporter extension which will automatically mount spiral directories to config.
     *
     * @param Core        $core
     * @param FileManager $file
     */
    public function __construct(Core $core, FileManager $file)
    {
        $this->core = $core;
        $this->file = $file;
    }

    /**
     * Pack scalar value to config.
     *
     * @param string $name
     * @param mixed  $value
     * @return string
     */
    protected function packValue($name, $value)
    {
        if (is_null($value))
        {
            $value = "null";
        }
        elseif (is_bool($value))
        {
            $value = ($value ? "true" : "false");
        }
        elseif (!is_numeric($value))
        {
            if (!is_string($value))
            {
                throw new \RuntimeException("Unable to pack non scalar value.");
            }

            $alias = $directory = $hasAlias = false;
            $directories = $this->core->getDirectories();

            foreach ($directories as &$directory)
            {
                $directory = $this->file->normalizePath($directory);
                unset($directory);
            }

            //Sorting to get longest first
            uasort($directories, function ($valueA, $valueB)
            {
                return strlen($valueA) < strlen($valueB);
            });

            foreach ($directories as $alias => $directory)
            {
                if (strpos($this->file->normalizePath($value), $directory) === 0)
                {
                    $hasAlias = true;
                    break;
                }
            }

            if (!$hasAlias)
            {
                $value = var_export($value, true);
            }
            elseif (!empty($directory))
            {
                $value = 'directory("' . $alias . '") . ' . var_export(
                        substr($value, strlen($directory)),
                        true
                    );
            }
        }

        return $name . $value;
    }
}