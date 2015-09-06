<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules\Prototypes;

use Psr\Log\LoggerAwareInterface;
use Spiral\Core\Component;
use Spiral\Database\Exceptions\MigratorException;
use Spiral\Database\Migrations\MigratorInterface;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\FileManager;
use Spiral\Files\FilesInterface;
use Spiral\Modules\ConfigWriter;
use Spiral\Modules\Exceptions\ConfigWriterException;
use Spiral\Modules\Exceptions\InstallerException;
use Spiral\Modules\InstallerInterface;
use Spiral\Modules\ModuleManager;

/**
 * Default spiral installer provides ability to alter application configs, register and update
 * module public files, create migrations and set of required component bindings.
 *
 * Installer requires FileManager implementation (relativePath method).
 */
class Installer extends Component implements InstallerInterface, LoggerAwareInterface
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
     * Declared module bindings, must be compatible with active container instance and be
     * serializable into array.
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
     * @var FileManager
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
     * @param FileManager       $file
     * @param ModuleManager     $modules
     * @param MigratorInterface $migrator
     * @param string            $moduleDirectory Module root directory.
     */
    public function __construct(
        FileManager $file,
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
    public function registerConfig(ConfigWriter $config, $load = true, $directory = '/config')
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

        if (!$this->files->exists($source)) {
            throw new InstallerException(
                "Unable to publish directory '{$directory}', no such module location."
            );
        }

        foreach ($this->files->getFiles($source) as $filename) {
            //Relative filename
            $relative = $this->files->relativePath($filename, $source);

            $this->publishFile(
                $this->files->relativePath($filename, $this->moduleDirectory),
                $destination . FilesInterface::SEPARATOR . $relative,
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
                "Unable to public file '{$source}'', file not found in module root directory."
            );
        }

        $fullDestination = $this->files->normalizePath(
            $this->modules->publicDirectory() . FilesInterface::SEPARATOR . $destination
        );

        $this->publicFiles[$this->files->normalizePath($destination)] = [
            'filename'    => $filename,
            'destination' => $fullDestination,
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
     * List of files which already exists in application public directory and conflicted with
     * modules files by size or content, this method should be called before module installation to
     * make sure that no user files will be removed or overwritten without notification. File
     * conflicts can be resolved by picking one of resolution methods.
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

            $conflicts[$file] = [
                'expected'    => [
                    'hash' => $definition['hash'],
                    'size' => $definition['size']
                ],
                'received'    => [
                    'hash' => $this->files->md5($definition['destination']),
                    'size' => $this->files->size($definition['destination'])
                ],
                'filename'    => $definition['filename'],
                'destination' => $definition['destination']
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
        $this->logger()->info("Publishing module files.");
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
            if (!$this->files->exists($definition['destination'])) {
                $this->logger()->debug(
                    "Publishing file '[module]{file}'.", compact('file')
                );
            } else {
                if ($this->files->md5($definition['destination']) == $definition['hash']) {
                    $this->logger()->debug(
                        "Module file '[module]{file}' already published.", compact('file')
                    );
                    continue;
                }

                if ($conflicts == self::IGNORE) {
                    $this->logger()->warning(
                        "file '[module]{file}' already published and different version, ignoring.",
                        compact('file')
                    );
                    continue;
                } else {
                    $this->logger()->warning(
                        "Module file '[module]{file}' already published and different version, overwriting.",
                        compact('file')
                    );
                }
            }

            //Copying using write() method to ensure directories and permissions, slower by easier
            $this->files->write(
                $definition['destination'],
                $this->files->read($definition['filename']),
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