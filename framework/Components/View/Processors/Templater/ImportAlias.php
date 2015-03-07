<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors\Templater;

use Spiral\Components\Files\FileManager;
use Spiral\Components\View\View;
use Spiral\Components\View\ViewException;

class ImportAlias
{
    /**
     * Import definition level, deeper in hierarchy import defined - higher level.
     *
     * @var int
     */
    public $level = 0;

    /**
     * View namespace to lookup for imports.
     *
     * @var string
     */
    public $namespace = '';

    /**
     * Import pattern, can be either view name or directory location (star [*] required in this case).
     *
     * @var string
     */
    protected $pattern = '';

    /**
     * Output pattern, all found tags will be formatted according to this patter.
     *
     * @var string
     */
    protected $outputPattern = '%s';

    /**
     * Cached list of tag aliases.
     *
     * @var array
     */
    protected static $aliases = array();

    /**
     * New alias object, will help system to alias blocks or directories
     * to node visibility zone.
     *
     * @param int   $level   Import lever (deeper node - higher level).
     * @param array $options Options grabbed from import tag.
     * @throws ViewException
     */
    public function __construct($level, array $options)
    {
        $this->level = $level;

        if (!isset($options['pattern']))
        {
            throw new ViewException("Unable to register import, no patter for view or directory specified.");
        }

        if (isset($options['namespace']))
        {
            $this->namespace = $options['namespace'];
        }

        $this->pattern = $options['pattern'];

        if (strpos($this->pattern, ':'))
        {
            list($this->namespace, $this->pattern) = explode(':', $this->pattern);
        }

        if (!$this->namespace)
        {
            $this->namespace = View::getInstance()->defaultNamespace();
        }

        if (isset($options['prefix']))
        {
            //Will work with directory imports, concat prefix with original view name
            $this->outputPattern = $options['prefix'] . '%s';
        }

        if (isset($options['alias']))
        {
            //Will work only with single views, strictly defined pattern
            $this->outputPattern = str_replace('%', '', $options['alias']);
        }
    }

    /**
     * Import request is for some directory with view files inside, every found file will be aliased according
     * outputPattern rule.
     *
     * @return bool
     */
    protected function isDirectory()
    {
        return strpos($this->pattern, '*') !== false;
    }

    /**
     * Copying import object to be used in another node, delta import used in cases if another node is child one.
     *
     * @param int $deltaLevel How import level changed.
     * @return ImportAlias
     */
    public function getCopy($deltaLevel = 0)
    {
        $import = clone $this;
        $import->level += $deltaLevel;

        return $import;
    }

    /**
     * Unique import id, used to store cached view aliases separately from other imports.
     *
     * @return string
     */
    protected function aliasID()
    {
        return md5($this->pattern . '-' . $this->namespace . '-' . $this->outputPattern);
    }

    /**
     * Will generate list of aliases associated with this import.
     *
     * @param View   $view
     * @param string $separator
     * @return array
     * @throws ViewException
     */
    public function generateAliases(View $view, $separator = '.')
    {
        $file = FileManager::getInstance();
        if (isset(self::$aliases[$this->aliasID()]))
        {
            return self::$aliases[$this->aliasID()];
        }

        if (!$this->isDirectory())
        {
            //Checking if we can detect our view
            $view->getFilename($this->namespace, $this->pattern, false);

            //Creating alias
            return self::$aliases[$this->aliasID()][sprintf($this->outputPattern, basename($this->pattern))] = $this->pattern;
        }

        if (strpos($this->outputPattern, '%s') === false)
        {
            throw new ViewException("Unable to use static alias '{$this->outputPattern}' while importing directory.");
        }

        //Checking if namespace exists
        if (!isset($view->getNamespaces()[$this->namespace]))
        {
            throw new ViewException("Undefined view namespace '{$this->namespace}'.");
        }

        $directories = $view->getNamespaces()[$this->namespace];

        $aliases = array();
        $directory = str_replace('*', '', $this->pattern);
        foreach ($directories as $lookupDirectory)
        {
            $targetDirectory = $file->normalizePath($lookupDirectory . '/' . $directory);
            if ($file->exists($targetDirectory))
            {
                //Getting views
                foreach ($file->getFiles($targetDirectory, array(substr(View::EXTENSION, 1))) as $filename)
                {
                    //Filename in namespace
                    $name = $file->relativePath($filename, $targetDirectory);
                    $filename = $file->relativePath($filename, $lookupDirectory);

                    //Removing ./ and .php
                    $name = str_replace(array('/', '\\'), $separator, substr($name, 2, -4));

                    //Registering alias
                    $aliases[sprintf($this->outputPattern, $name)] = substr($filename, 2, -4);
                }
            }
        }

        if (!$aliases)
        {
            throw new ViewException("No views were found under directory '{$directory}' in namespace '{$this->namespace}'.");
        }

        return self::$aliases[$this->aliasID()] = $aliases;
    }

    /**
     * Associated view namespace.
     *
     * @return array
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
}