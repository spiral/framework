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

/**
 * Default environment implementation wraps at top of DotEnv package and caches env values into
 * application memory. Based on my tests it can speed up application in 1.4-1.9 times.
 *
 * Attention, this implementation works using global _ENV array.
 */
class DotenvEnvironment extends Environment
{
    /**
     * Environment section.
     */
    const MEMORY = 'environment';

    /**
     * Environment filename.
     *
     * @var string
     */
    private $filename = '';

    /**
     * @invisible
     * @var MemoryInterface|null
     */
    protected $memory = null;

    /**
     * @param string               $filename
     * @param MemoryInterface|null $memory Keep empty to disable cache.
     *
     * @throws EnvironmentException
     */
    public function __construct(string $filename, MemoryInterface $memory = null)
    {
        $this->filename = $filename;
        $this->memory = $memory;

        parent::__construct();
    }

    /**
     * Fetch environment values from .evn file.
     *
     * @param string $filename
     *
     * @return array
     */
    protected function parseValues($filename)
    {
        //Extends Dotenv loader
        $parser = new Parser($filename);

        return $parser->parse();
    }

    /**
     * Load environment data.
     *
     * @throws EnvironmentException
     */
    protected function load()
    {
        if (!file_exists($this->filename)) {
            parent::load();

            //Nothing to load
            return;
        }

        //Out env id is based on .env file content
        $this->id = md5_file($this->filename);

        if (
            !empty($this->memory)
            && !empty($values = $this->memory->loadData(static::MEMORY . '.' . $this->id))
        ) {
            //Restore from cache
            $this->initEnvironment($values);

            return;
        }

        //Load env variables from filename
        $values = array_merge($_ENV, $this->parseValues($this->filename));
        $this->initEnvironment($values);

        if (!empty($this->memory)) {
            $this->memory->saveData(static::MEMORY . '.' . $this->id, $values);
        }
    }

    /**
     * Initiate environment values.
     *
     * @param array $values
     *
     * @return array
     */
    protected function initEnvironment(array $values): array
    {
        foreach ($values as $name => &$value) {
            $this->set($name, $value);
            unset($value);
        }

        return $values;
    }
}