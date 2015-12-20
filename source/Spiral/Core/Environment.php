<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Environment\Parser;
use Spiral\Core\Exceptions\EnvironmentException;
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
     * Enviroment id
     *
     * @var string
     */
    private $id = '';

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
            throw new EnvironmentException("Unable to load environment, file is missing");
        }

        //Unique env file hash
        $this->id = $this->files->md5($this->filename);

        if (!empty($values = $this->memory->loadData($this->id, static::MEMORY_SECTION))) {
            //Restore from cache
            $this->initEnvironment($values);

            return $this;
        }

        //Load env values using DotEnv extension
        $values = $this->initEnvironment(
            $this->parseValues($this->filename)
        );

        $this->memory->saveData($this->id, $values, static::MEMORY_SECTION);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function set($name, $value)
    {
        $_ENV[$name] = $value;
        putenv("$name=$value");

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
     * @return array
     */
    protected function initEnvironment(array $values)
    {
        foreach ($values as $name => &$value) {
            $value = $this->normalize($value);
            $this->set($name, $value);
            unset($value);
        }

        return $values;
    }

    /**
     * Normalize env value.
     *
     * @param string $value
     * @return bool|null|string
     */
    private function normalize($value)
    {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'null':
            case '(null)':
                return null;

            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }
}