<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Core;
use Spiral\Support\Generators\Config\ConfigWriter;
use Spiral\Support\Generators\Config\ConfigWriterException;

/**
 * To be used in modules for altering view configs.
 */
class ViewConfig extends ConfigWriter
{


    /**
     * Methods will be applied to merge existed and custom configuration data in merge method is
     * specified as Config::mergeCustom. This method usually used to perform logical merge.
     *
     * @param mixed $internal Requested configuration data.
     * @param mixed $existed  Existed configuration data.
     * @return mixed
     */
    protected function customMerge($internal, $existed)
    {
        $result = $existed;

        foreach ($this->namespaces as $namespace => $directory)
        {
            $directory = $this->file->normalizePath($this->baseDirectory . '/' . $directory, true);

            $result['namespaces'][$namespace][] = $directory;
            foreach ($result['namespaces'][$namespace] as &$namespaceDirectory)
            {
                $namespaceDirectory = $this->file->normalizePath($namespaceDirectory, true);
                unset($namespaceDirectory);
            }

            $result['namespaces'][$namespace] = array_unique($result['namespaces'][$namespace]);
        }

        //Adding new engines
        $result['engines'] += $this->engines;

        //Processors
        if (isset($result['engines'][self::DEFAULT_ENGINE]['processors']))
        {
            $result['engines'][self::DEFAULT_ENGINE]['processors'] += $this->processors;
        }

        return $result;
    }
}