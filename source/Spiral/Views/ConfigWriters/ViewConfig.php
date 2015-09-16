<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\ConfigWriters;

use Spiral\Core\Core;
use Spiral\Files\FilesInterface;
use Spiral\Modules\ConfigSerializer;
use Spiral\Modules\ConfigWriter;
use Spiral\Modules\Exceptions\ConfigWriterException;
use Spiral\Tokenizer\TokenizerInterface;
use Spiral\Views\ViewManager;

/**
 * ConfigWriter with specified to ViewManager functions. Provides simplified way to register view
 * namespace, engine or spiral Compiler processor. You must specify module root directory in order
 * to register view namespaces using relative path.
 */
class ViewConfig extends ConfigWriter
{
    /**
     * View namespaces associated with their view directories.
     *
     * @var array
     */
    private $namespaces = [];

    /**
     * View engines to be registered in view config.
     *
     * @var array
     */
    private $engines = [];

    /**
     * View processors to be added (if not already) to the end of processor list in spiral Compiler
     * config.
     *
     * @var array
     */
    private $processors = [];

    /**
     * Base views directory (module root directory).
     *
     * @var string
     */
    protected $baseDirectory = '';

    /**
     * @param string             $baseDirectory
     * @param int                $method
     * @param ConfigSerializer   $serializer
     * @param Core               $core
     * @param FilesInterface     $files
     * @param TokenizerInterface $tokenizer
     */
    public function __construct(
        $baseDirectory,
        $method = self::MERGE_FOLLOW,
        ConfigSerializer $serializer,
        Core $core,
        FilesInterface $files,
        TokenizerInterface $tokenizer
    ) {
        $this->baseDirectory = $baseDirectory;
        parent::__construct(
            ViewManager::CONFIG, self::MERGE_CUSTOM, $serializer, $core, $files, $tokenizer
        );
    }

    /**
     * Register view namespace relatively to base views directory (module root directory).
     *
     * Examples:
     * $viewConfig->registerNamespace('keeper', 'views');
     *
     * @param string $namespace View namespace.
     * @param string $directory Directory name relative to base views directory.
     * @return $this
     * @throws ConfigWriterException
     */
    public function registerNamespace($namespace, $directory = 'views')
    {
        if (!isset($this->namespaces[$namespace])) {
            $this->namespaces[$namespace] = [];
        }

        $location = $this->files->normalizePath(
                $this->baseDirectory . FilesInterface::SEPARATOR . $directory
            ) . '/';

        if (!$this->files->exists($location)) {
            throw new ConfigWriterException(
                "Unable to register view namespace '{$namespace}', no such directory '{$directory}'."
            );
        }

        $this->namespaces[$namespace][] = $location;

        return $this;
    }

    /**
     * Register new view engine linked to specified file extensions.
     *
     * @param string $name
     * @param array  $extensions
     * @param string $compiler
     * @param string $view
     * @param array  $options Custom engine options.
     */
    public function registerEngine($name, array $extensions, $compiler, $view, array $options = [])
    {
        $this->engines[$name] = [
                'extensions' => $extensions,
                'compiler'   => $compiler,
                'view'       => $view
            ] + $options;
    }

    /**
     * Register new view processor for default spiral Compiler.
     *
     * @param string $class
     * @param array  $options Custom processor options.
     */
    public function registerProcessor($class, $options = [])
    {
        $this->processors[$class] = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig($directory, $name = null)
    {
        //No need to read module view config
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Performs logical view config merge.
     */
    protected function merge($config, $original)
    {
        $config = $original;

        foreach ($this->namespaces as $namespace => $directories) {
            foreach ($directories as $directory) {
                $config['namespaces'][$namespace][] = $directory;
            }

            //To filter non unique namespaces
            foreach ($config['namespaces'][$namespace] as &$directory) {
                $directory = $this->files->normalizePath($directory) . '/';
                unset($directory);
            }

            //Dropping duplicates
            $config['namespaces'][$namespace] = array_unique($config['namespaces'][$namespace]);
        }

        //Engines
        $config['engines'] += $this->engines;

        //Processors
        foreach ($this->processors as $processor => $options) {
            if (!isset($config['compiler']['processors'][$processor])) {
                $config['compiler']['processors'][$processor] = $options;
            }
        }

        return $config;
    }
}