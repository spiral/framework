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
use Spiral\Components\View\ViewManager;
use Spiral\Components\View\ViewException;

class NamespaceImport extends Import
{
    /**
     * Directory to be checked.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * Local node namespace.
     *
     * @var string
     */
    protected $outerNamespace = '';

    /**
     * Cached list of tag aliases.
     *
     * @var array
     */
    protected static $aliases = [];

    /**
     * Namespace import used to declare local node namespaces binded to specified view namespace
     * or directory. Usually used to import set of tags.
     *
     * @param int    $level
     * @param string $namespace
     * @param string $path
     * @param string $name
     */
    public function __construct($level, $namespace, $path, $name = '')
    {
        $this->level = $level;

        if (strpos($path, ':') !== false)
        {
            list($this->namespace, $this->directory) = explode(':', $path);
        }
        else
        {
            $this->namespace = $path;
        }

        $this->outerNamespace = $name;
        if (empty($this->outerNamespace))
        {
            $this->outerNamespace = $this->namespace;
        }

        if ($this->namespace == self::SELF_NAMESPACE)
        {
            //Let's use parent namespace
            $this->namespace = $namespace;
        }
    }

    /**
     * Unique import id, used to store cached view aliases separately from other imports.
     *
     * @return string
     */
    protected function aliasID()
    {
        return md5($this->namespace . '-' . $this->directory . '-' . $this->outerNamespace);
    }

    /**
     * Will generate list of aliases associated with this import.
     *
     * @param ViewManager $manager
     * @param FileManager $file
     * @param string      $separator
     * @return array
     * @throws ViewException
     */
    public function generateAliases(ViewManager $manager, FileManager $file, $separator = '.')
    {
        if (isset(self::$aliases[$this->aliasID()]))
        {
            return self::$aliases[$this->aliasID()];
        }

        //Checking if namespace exists
        if (!isset($manager->getNamespaces()[$this->namespace]))
        {
            throw new ViewException("Undefined view namespace '{$this->namespace}'.");
        }

        $directories = $manager->getNamespaces()[$this->namespace];

        $aliases = [];
        foreach ($directories as $lookupDirectory)
        {
            $targetDirectory = $file->normalizePath($lookupDirectory . '/' . $this->directory);
            if ($file->exists($targetDirectory))
            {
                $viewFiles = $file->getFiles($targetDirectory);

                //Getting views
                foreach ($viewFiles as $filename)
                {
                    //Filename in namespace
                    $name = $file->relativePath($filename, $targetDirectory);
                    $filename = $file->relativePath($filename, $lookupDirectory);

                    //Removing ./ and .php
                    $name = substr($name, 2, -1 * (strlen($file->extension($name)) + 1));
                    $filename = substr($filename, 2, -1 * (strlen($file->extension($filename)) + 1));

                    $name = str_replace(['/', '\\'], $separator, $name);
                    $filename = str_replace(['/', '\\'], $separator, $filename);

                    //Registering alias
                    $aliases[$this->outerNamespace . ':' . $name] = $this->namespace . ':' . $filename;
                }
            }
        }

        if (empty($aliases))
        {
            throw new ViewException(
                "No views were found "
                . "under directory '{$this->directory}' in namespace '{$this->namespace}'."
            );
        }

        return self::$aliases[$this->aliasID()] = $aliases;
    }
}