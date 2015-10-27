<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

use Spiral\Core\Component;
use Spiral\Core\Core;
use Spiral\Files\FilesInterface;
use Spiral\Modules\Exceptions\ConfigWriterException;
use Spiral\Reactor\Exceptions\SerializeException;
use Spiral\Tokenizer\TokenizerInterface;

/**
 * ConfigWriter classes dedicated to simplify operations with configuration files stored in
 * application directory. They can work only with physically stored configs.
 *
 * At this moment config writer can:
 * - merge config content with user specified data
 * - replace config data
 * - keeper config file comment header between rewrites
 * - mount directory() function as alias in paths
 *
 * So components has custom implementation of ConfigWrites to simplify config updates even more.
 *
 * @see ConfigWriter::merge()
 */
class ConfigWriter extends Component
{
    /**
     * New config values will replace already existed config sections.
     */
    const MERGE_REPLACE = 1;

    /**
     * Already existed config sections and values will replace new values.
     */
    const MERGE_FOLLOW = 2;

    /**
     * Custom merging function will be performed.
     */
    const MERGE_CUSTOM = 3;

    /**
     * Entirely overwrite config data, do not respect any old data.
     */
    const FULL_OVERWRITE = 4;

    /**
     * Config file name, should not include file extension but may have directory included.
     *
     * @var string
     */
    private $name = '';

    /**
     * How config should be processed compared to already existed one.
     *
     * @var int|string
     */
    private $method = self::MERGE_FOLLOW;

    /**
     * Config file header should include php tag declaration and may contain doc comment describing
     * config sections. Doc comment will be automatically fetched from application config if it
     * already exists.
     *
     * @var string
     */
    protected $header = "<?php\n";

    /**
     * Config specified by external module, user and etc. Will be merged with application config
     * if such exists.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var ConfigSerializer
     */
    protected $serializer = null;

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
     * @invisible
     * @var TokenizerInterface
     */
    protected $tokenizer = null;

    /**
     * @param string             $name
     * @param int                $method How writer should merge existed and requested config
     *                                   contents.
     * @param ConfigSerializer   $serializer
     * @param Core               $core
     * @param FilesInterface     $files
     * @param TokenizerInterface $tokenizer
     */
    public function __construct(
        $name,
        $method = self::MERGE_FOLLOW,
        ConfigSerializer $serializer,
        Core $core,
        FilesInterface $files,
        TokenizerInterface $tokenizer
    ) {
        $this->name = $name;
        $this->method = $method;

        $this->serializer = $serializer;
        $this->core = $core;
        $this->files = $files;
        $this->tokenizer = $tokenizer;
    }

    /**
     * Config file name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set configuration data to be merged with application config if such exists.
     *
     * @param array $config Configuration data.
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * New configuration data.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Read config file to create configuration data to be merged with application config if such
     * exists.
     *
     * @param string $directory Director where config should be located.
     * @param string $name      Config name, outer config name will be used by default.
     * @return $this
     * @throws ConfigWriterException
     */
    public function loadConfig($directory, $name = null)
    {
        if (empty($name)) {
            $name = $this->name;
        }

        $filename = $this->configFilename($directory, $name);
        if (!$this->files->exists($filename)) {
            throw new ConfigWriterException(
                "Unable to load '{$name}' configuration, file not found."
            );
        }

        $this->setConfig(require $filename)->readHeader($filename);

        return $this;
    }

    /**
     * Export configuration data to final directory (application configs directory by default). If
     * configuration file already exists it's content will be merged with new configuration data
     * using merge method.
     *
     * @param string $directory Destination config directory, application directory by default.
     * @param int    $mode      File mode, use FilesInterface::RUNTIME for publicly accessible
     *                          files.
     * @return bool
     */
    public function writeConfig($directory = null, $mode = FilesInterface::READONLY)
    {
        $directory = !empty($directory) ? $directory : $this->core->directory('config');

        //Target configuration file
        $filename = $this->configFilename($directory, $this->name);

        $original = [];
        if ($this->files->exists($filename)) {
            $original = (require $filename);

            //We are going to use original config header
            $this->readHeader($filename);
        }

        return $this->files->write($filename, $this->renderConfig($original), $mode, true);
    }

    /**
     * Render configuration file content with it's header and data (can be automatically merged with
     * original configuration). Config content will be merged based on merge rule.
     *
     * @param mixed $original
     * @return string
     * @throw ConfigWriterException
     * @throws SerializeException
     */
    protected function renderConfig($original = null)
    {
        $config = $this->config;
        if (!empty($original)) {
            //Merging configs
            $config = $this->mergeConfigs($config, $original);
        }

        return interpolate("{header}return {config};", [
            'header' => $this->header,
            'config' => $this->serializer->serialize($config)
        ]);
    }

    /**
     * Merge new config data with original one. Merge method can be defined when Config class
     * created.
     *
     * @param mixed $config   Requested configuration data.
     * @param mixed $original Existed (original) configuration data.
     * @return mixed
     * @throw ConfigWriterException
     */
    protected function mergeConfigs($config, $original)
    {
        $result = null;

        switch ($this->method) {
            case self::FULL_OVERWRITE:
                //Full replacement
                $result = $config;
                break;

            case self::MERGE_CUSTOM:
                //Using custom (logical merger)
                $result = $this->merge($config, $original);
                break;

            case self::MERGE_FOLLOW:
                //Using original values in priority
                $result = $original;
                if (is_array($config) && is_array($original)) {
                    $result = array_replace_recursive($original, $config, $original);
                }
                break;

            case self::MERGE_REPLACE:
                //Using new values in priority
                $result = $config;
                if (is_array($config) && is_array($original)) {
                    $result = array_replace_recursive($config, $original, $config);
                }
                break;
        }

        return $result;
    }

    /**
     * Methods will be applied to merge existed and custom configuration data if merge method is
     * specified as Config::MERGE_CUSTOM. This method usually used to perform logical merge.
     *
     * @param mixed $config   Requested configuration data.
     * @param mixed $original Existed (original) configuration data.
     * @return mixed
     * @throws ConfigWriterException
     */
    protected function merge($config, $original)
    {
        throw new ConfigWriterException("No merging function defined.");
    }

    /**
     * Load configuration doc headers from existed file.
     *
     * @param string $filename
     * @return $this
     */
    protected function readHeader($filename)
    {
        $this->header = '';
        foreach ($this->tokenizer->fetchTokens($filename) as $token) {
            if (isset($token[0]) && $token[0] == T_RETURN) {
                //End of header
                break;
            }

            $this->header .= $token[TokenizerInterface::CODE];
        }

        return $this;
    }

    /**
     * Generate config filename using config directory and config name. PHP extension will be added
     * here.
     *
     * @param string $directory
     * @param string $name
     * @return string
     */
    private function configFilename($directory, $name)
    {
        return $this->files->normalizePath(
            $directory . FilesInterface::SEPARATOR
            . (!empty($name) ? $name : $this->name) . '.' . Core::EXTENSION
        );
    }
}