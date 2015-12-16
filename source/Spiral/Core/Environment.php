<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Environment\Parser;
use Spiral\Files\FilesInterface;

/**
 * Default environment implementation wraps at top of DotEnv package and caches env values into
 * application memory. Based on my tests it can speed up application in 1.4-1.9 times.
 *
 * Attention, this implementation works using global _ENV array.
 *
 * @todo Work on immutable environment values. Or ignore it.
 */
class Environment implements EnvironmentInterface
{
    /**
     * Environment section.
     */
    const MEMORY_SECTION = 'environment';

    /**
     * Environment filename.
     *
     * @var string
     */
    private $filename = '';

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * @param string               $filename
     * @param FilesInterface       $files
     * @param HippocampusInterface $memory
     */
    public function __construct($filename, FilesInterface $files, HippocampusInterface $memory)
    {
        $this->filename = $filename;
        $this->files = $files;
        $this->memory = $memory;
    }

    /**
     * Load environment data.
     *
     * @return $this
     */
    public function load()
    {
        if (!$this->files->exists($this->filename)) {
            //Nothing to load
            return $this;
        }

        //Unique env file hash
        $hash = $this->files->md5($this->filename);

        if (!empty($values = $this->memory->loadData($hash, static::MEMORY_SECTION))) {
            //Restore from cache
            $this->initEnvironment($values);

            return $this;
        }

        //Load env values using DotEnv extension
        $this->initEnvironment(
            $values = $this->parseValues($this->filename)
        );

        $this->memory->saveData($hash, $values, static::MEMORY_SECTION);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function set($name, $value)
    {
        $_ENV[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $_ENV)) {
            return $_ENV[$name];
        }

        return $default;
    }

    /**
     * Fetch environment values from .evn file.
     *
     * @param string $filename
     * @return array
     */
    protected function parseValues($filename)
    {
        //Extends Dotenv loader
        $parser = new Parser($filename);

        return $parser->parse();
    }

    /**
     * Initiate environment values.
     *
     * @param array $values
     */
    protected function initEnvironment(array $values)
    {
        foreach ($values as $name => $value) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}