<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Reactor\ConfigWriters;

use Spiral\Core\Core;
use Spiral\Files\FilesInterface;
use Spiral\Modules\ConfigSerializer;
use Spiral\Modules\ConfigWriter;
use Spiral\Reactor\Reactor;
use Spiral\Tokenizer\TokenizerInterface;

/**
 * Provides functionality to register new generator with it's class, namespace and etc.
 */
class ReactorConfig extends ConfigWriter
{
    /**
     * Added generators.
     *
     * @var array
     */
    protected $generators = [];

    /**
     * @param ConfigSerializer   $serializer
     * @param Core               $core
     * @param FilesInterface     $files
     * @param TokenizerInterface $tokenizer
     */
    public function __construct(
        ConfigSerializer $serializer,
        Core $core,
        FilesInterface $files,
        TokenizerInterface $tokenizer
    ) {
        parent::__construct(
            Reactor::CONFIG,
            self::MERGE_CUSTOM,
            $serializer,
            $core,
            $files,
            $tokenizer
        );
    }

    /**
     * Register new reactor generator.
     *
     * @param string $generator
     * @param string $namespace
     * @param string $postfix
     * @param string $directory
     */
    public function registerGenerator($generator, $namespace, $postfix, $directory)
    {
        $this->generators[$generator] = compact('namespace', 'postfix', 'directory');
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig($directory, $name = null)
    {
        //No need to read module reactor config
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function merge($config, $original)
    {
        return $original + ['generators' => $this->generators];
    }
}