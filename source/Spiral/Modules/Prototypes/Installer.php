<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules\Prototypes;

use Spiral\Core\Component;
use Spiral\Database\Exceptions\MigratorException;
use Spiral\Database\Migrations\MigratorInterface;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\FilesInterface;
use Spiral\Modules\ConfigWriter;
use Spiral\Modules\Exceptions\ConfigWriterException;
use Spiral\Modules\Exceptions\InstallerException;
use Spiral\Modules\InstallerInterface;
use Spiral\Modules\ModuleManager;

/**
 * Default spiral installer provides ability to alter application configs, register and update module
 * public files, create migrations and set of required component bindings.
 */
class Installer extends Component implements InstallerInterface
{
    /**
     * Installer will log it's installation/update operations.
     */
    use LoggerTrait;

    /**
     * Directory where module located in, all public files, configs and views should be defined
     * relative to this directory.
     *
     * @var string
     */
    private $moduleDirectory = '';

    /**
     * Flag to indicate that following module requires bootstrap() method call on application
     * initialization.
     *
     * @var bool
     */
    private $boostrappable = false;

    /**
     * Declared module bindings, must be compatible with active container instance and be serializable
     * into array.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Files to be moved into application webroot directory.
     *
     * @var array
     */
    protected $publicFiles = [];

    /**
     * ConfigWriters required for module installation.
     *
     * @var ConfigWriter[]
     */
    protected $configs = [];

    /**
     * Migration classes to be registered in Migrator.
     *
     * @var array
     */
    protected $migrations = [];

    /**
     * @invisible
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @invisible
     * @var ModuleManager
     */
    protected $modules = null;

    /**
     * @invisible
     * @var MigratorInterface
     */
    protected $migrator = null;

    /**
     * @param FilesInterface    $file
     * @param ModuleManager     $modules
     * @param MigratorInterface $migrator
     * @param string            $moduleDirectory Module root directory.
     */
    public function __construct(
        FilesInterface $file,
        ModuleManager $modules,
        MigratorInterface $migrator,
        $moduleDirectory
    ) {
        $this->files = $file;
        $this->modules = $modules;
        $this->migrator = $migrator;

        $this->moduleDirectory = $this->files->normalizePath($moduleDirectory);
    }

    /**
     * Module root directory (every public file should be added relatively to this directory).
     *
     * @return string
     */
    public function moduleDirectory()
    {
        return $this->moduleDirectory;
    }

    /**
     * Declare that module required bootstrapping every time core is loaded.
     *
     * @param bool $require
     * @return $this
     */
    public function requireBootstrap($require = null)
    {
        $this->boostrappable = $require;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function needsBootstrapping()
    {
        return $this->boostrappable;
    }

    /**
     * Request binding to be added in order to make Module work properly, syntax must be compatible
     * with active container instance. Only class to class bindings are allowed.
     *
     * @see ContainerInterface
     * @param string $alias Alias where singleton will be attached to.
     * @param string $resolver
     * @return $this
     */
    public function addBinding($alias, $resolver)
    {
        $this->bindings[$alias] = $resolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Register new config writer to alter or create application configuration.
     *
     * @see ConfigWriter
     * @param ConfigWriter $config
     * @param bool         $load      Load config data from module configuration directory.
     * @param string       $directory Module config directory relative to module root location.
     * @return $this
     * @throws ConfigWriterException
     */
    public function registerConfig(ConfigWriter $config, $load = true, $directory = '/configs')
    {
        if ($load) {
            //Trying to load config data from module files
            $config->loadConfig($this->moduleDirectory . $directory);
        }

        $this->configs[] = $config;

        return $this;
    }

    /**
     * Every registered config writer.
     *
     * @return ConfigWriter[]
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Register new database migration based on it's class name. Migrations will be mounted only
     * when module will be installer (not updated) and must be executed manually.
     *
     * Example:
     * $installer->registerMigration('blog_posts', 'Vendor\Blog\Migrations\BlogPostsMigration');
     *
     * @param string $name      Migration name.
     * @param string $migration Migration class name (must be reachable by framework).
     * @return $this
     */
    public function registerMigration($name, $migration)
    {
        $this->migrations[$name] = $migration;

        return $this;
    }

    /**
     * List of migration classes to be registered in Migrator on module installation.
     *
     * @return array
     */
    public function getMigrations()
    {
        return $this->migrations;
    }

    /**
     * Public module directory and all it's files into application webroot directory. Directory
     * Directory must be located in module root and defined by relative name (to moduleDirectory).
     *
     * Examples:
     * $installer->publishDirectory(
     *      'resources',
     *      '/resources/scripts/plugin/',
     *      FilesInterface::RUNTIME
     * );
     *
     * Quick example:
     * $installer->publishDirectory('public'); //Every file in public directory will be published
     *
     * @see publishFile()
     * @see moduleDirectory()
     * @param string|null $directory   Source directory relative to modules directory.
     * @param string      $destination Destination filename relative to "webroot" directory.
     * @param int         $mode        File mode.
     * @return $this
     * @throws InstallerException
     */
    public function publishDirectory(
        $directory,
        $destination = '/',
        $mode = FilesInterface::READONLY
    ) {

        //Full name
        $source = $this->moduleDirectory . FilesInterface::SEPARATOR . $directory;
        $source = $this->files->normalizePath($source);

        if ($this->files->exists($source)) {
            throw new InstallerException(
                "Unable to publish directory '{$directory}', no such folder."
            );
        }

        //Inner source directories used as filename prefixes
        $innerDirectory = substr($source, strlen($this->moduleDirectory));
        foreach ($this->files->getFiles($directory) as $filename) {

            //Relative filename
            $filename = substr($filename, strlen($directory));

            $this->publishFile(
                $innerDirectory . FilesInterface::SEPARATOR . $filename,
                $destination . FilesInterface::SEPARATOR . $filename,
                $mode
            );
        }

        return $this;
    }

    /**
     * Register new public module file. File will moved to application webroot directory when module
     * will be installed or updated and replace existed file if allowed.
     *
     * File must be located in module directory and defined by relative name (to moduleDirectory).
     * Destination may differ from original filename or have specified file permissions.
     *
     * Examples:
     * $installer->registerFile(
     *      'resources/script.js',
     *      '/resources/scripts/plugin/script.js',
     *      FilesInterface::RUNTIME
     * );
     *
     * @see moduleDirectory()
     * @param string $source      Source filename relative to modules directory.
     * @param string $destination Destination filename relative to "webroot" directory.
     * @param int    $mode        File mode.
     * @return $this
     * @throws InstallerException
     */
    public function publishFile($source, $destination, $mode = FilesInterface::READONLY)
    {
        $source = $this->files->normalizePath($source);

        //Full path to module filename
        $filename = $this->files->normalizePath(
            $this->moduleDirectory . FilesInterface::SEPARATOR . $source
        );

        if (!$this->files->exists($filename)) {
            throw new InstallerException(
                "Unable to public file '{$source}'', file not found in module directory."
            );
        }

        $this->files[$this->files->normalizePath($destination)] = [
            'filename'    => $filename,
            'name'        => $source,
            'destination' => $this->modules->webrootDirectory() . FilesInterface::SEPARATOR . $destination,
            'hash'        => $this->files->md5($filename),
            'size'        => $this->files->size($filename),
            'mode'        => $mode
        ];

        return $this;
    }

    /**
     * List of public module files to be moved into application webroot directory.
     *
     * @return array
     */
    public function publicFiles()
    {
        return $this->publicFiles;
    }

    /**
     * List of files which already exists in application public directory and conflicted with modules
     * files by size or content, this method should be called before module installation to make sure
     * that no user files will be removed or overwritten without notification. File conflicts can be
     * resolved by picking one of resolution methods.
     *
     * @return array
     */
    public function fileConflicts()
    {
        $conflicts = [];
        foreach ($this->publicFiles as $file => $definition) {
            if (!$this->files->exists($definition['destination'])) {
                //No files exists
                continue;
            }

            if ($this->files->md5($definition['destination']) == $definition['hash']) {
                //Files are identical
                continue;
            }

            $conflicts[] = [
                'expected'    => [
                    'hash' => $definition['md5Hash'],
                    'size' => $definition['size']
                ],
                'received'    => [
                    'hash' => $this->files->md5($file['destination']),
                    'size' => $this->files->size($file['destination'])
                ],
                'source'      => $definition['source'],
                'name'        => $definition['name'],
                'destination' => $file['destination']
            ];
        }

        return $conflicts;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConfigWriterException
     * @throws MigratorException
     */
    public function install($conflicts = self::OVERWRITE)
    {
        $this->logger()->info("Mounting configurations.");
        $this->mountConfigs();

        $this->logger()->info("Mounting migrations.");
        $this->mountMigrations();

        $this->update($conflicts);
    }

    /**
     * {@inheritdoc}
     */
    public function update($conflicts = self::OVERWRITE)
    {
        $this->logger()->info("Mounting public module files.");
        $this->publishFiles($conflicts);
    }

    /**
     * Publish every registered file.
     *
     * @param int $conflicts Conflicts resolution method.
     * @throws InstallerException
     */
    protected function publishFiles($conflicts = self::OVERWRITE)
    {
        if (!empty($this->fileConflicts()) && $conflicts == self::NONE) {
            throw new InstallerException(
                "Unable to publish module files, unresolved conflicts presented (no conflicts tactic)."
            );
        }

        foreach ($this->publicFiles as $file => $definition) {
            //Module (relative) filename
            $name = $definition['name'];
            $source = $definition['source'];

            //Full path to file destination
            $destination = $definition['destination'];

            if (!$this->files->exists($file)) {
                $this->logger()->debug(
                    "Publishing file '[module]/{name}' to '{file}'.", compact('name', 'file')
                );
            } else {
                if ($this->files->md5($destination) == $definition['hash']) {
                    $this->logger()->debug(
                        "Module file '[module]/{name}' already published.", compact('name', 'file')
                    );
                    continue;
                }

                if ($conflicts == self::IGNORE) {
                    self::logger()->warning(
                        "file '[module]/{name}' already published and different version, ignoring.",
                        compact('name', 'file')
                    );
                    continue;
                } else {
                    self::logger()->warning(
                        "Module file '[module]/{name}' already published and different version, overwrite.",
                        compact('name', 'file')
                    );
                }
            }

            //Copying using write() method to ensure directories and permissions, slower by easier
            $this->files->write(
                $file,
                $this->files->read($source),
                $definition['mode'],
                true
            );
        }
    }

    /**
     * Execute declared configs writers.
     *
     * @throws ConfigWriterException
     */
    protected function mountConfigs()
    {
        foreach ($this->configs as $config) {
            $config->writeConfig();
            $this->logger()->debug("Updating configuration '{config}'.", [
                'config' => $config->getName()
            ]);
        }
    }

    /**
     * Mount declared migrations.
     *
     * @throws MigratorException
     */
    protected function mountMigrations()
    {
        foreach ($this->migrations as $name => $migration) {
            $this->migrator->registerMigration($name, $migration);

            $this->logger()->debug("Mounting migration '{name}'.", compact('name', 'migration'));
        }
    }
}