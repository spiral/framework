<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Migrations;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Helpers\StringHelper;

class Repository extends Component
{
    /**
     * Logging.
     */
    use Component\LoggerTrait;

    /**
     * Migrations file name format. This format will be used when requesting new migration filename.
     */
    const FILENAME_FORMAT = '{timestamp}{chunk}_{name}.php';

    /**
     * Migrations directory, declared in DBAL configuration.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * FileManager component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * Tokenizer component.
     *
     * @var Tokenizer
     */
    protected $tokenizer = null;

    /**
     * MigrationRepository instance. Manager responsible for registering and retrieving existed migrations.
     *
     * @param string      $directory
     * @param FileManager $file
     * @param Tokenizer   $tokenizer
     */
    public function __construct($directory, FileManager $file, Tokenizer $tokenizer)
    {
        $this->directory = $directory;
        $this->file = $file;
        $this->tokenizer = $tokenizer;
    }

    /**
     * Get list of migration classes associated with their filenames.
     *
     * @return array
     */
    public function getMigrations()
    {
        $migrations = array();

        foreach ($this->file->getFiles($this->directory, array('php')) as $filename)
        {
            $reflection = $this->tokenizer->fileReflection($filename);
            $migrations[] = $this->parseFilename(basename($filename)) + array(
                    'class'    => $reflection->getClasses()[0],
                    'filename' => basename($filename)
                );
        }

        return $migrations;
    }

    /**
     * Get migration instance. Migration array should contain base filename and class name.
     *
     * @see getMigrations()
     * @param array $migration
     * @return Migration
     */
    public function getMigration(array $migration)
    {
        if (!class_exists($migration['class'], false))
        {
            //Can happen sometimes
            require_once($this->directory . '/' . $migration['filename']);
        }
        else
        {
            self::logger()->warning(
                "Migration '{class}' already presented in loaded classes.",
                $migration
            );
        }

        return Container::getInstance()->get($migration['class']);
    }

    /**
     * Request new migration filename based on user input and current timestamp.
     *
     * @param string $name
     * @param string $chunk Additional string attached after timestamp, should be used when creating
     *                      many migrations at once to ensure correct order. Empty by default.
     * @param bool   $path  Full filename path will be returned.
     * @return string
     */
    public function getFilename($name, $chunk = '', $path = false)
    {
        $name = StringHelper::url($name, '_');

        $filename = StringHelper::interpolate(self::FILENAME_FORMAT, array(
            'timestamp' => date('Ymd_His'),
            'chunk'     => $chunk,
            'name'      => $name
        ));

        return ($path ? $this->directory . '/' : '') . $filename;
    }

    /**
     * Parse filename to fetch timestamp and migration name.
     *
     * @param array $filename
     * @return array
     */
    protected function parseFilename($filename)
    {
        $filename = explode('_', substr($filename, 0, -4));

        $timestamp = \DateTime::createFromFormat('Ymd_His', $filename[0] . '_' . $filename[1])
            ->getTimestamp();

        return array(
            'name'      => join('_', array_slice($filename, 2)),
            'timestamp' => $timestamp
        );
    }

    /**
     * Help method used to register new migration by class name. Entire class declaration will be
     * copied to reserved filename. There is no limitations of class name or namespace to use as
     * Tokenizer component will be used to resolve target name.
     *
     * Examples:
     * $repository->registerMigration('create_blog_tables', 'Vendor\Blog\Migrations\BlogTables');
     *
     * @param string $name  Migration name.
     * @param string $class Class name to represent migration.
     * @return string
     * @throws MigrationException
     */
    public function registerMigration($name, $class)
    {
        if (!class_exists($class))
        {
            throw new MigrationException(
                "Unable to register migration, representing class does not exists."
            );
        }

        foreach ($this->getMigrations() as $migration)
        {
            if ($migration['class'] = $class)
            {
                //Already presented
                return false;
            }
        }

        $filename = $this->getFilename($name);

        //Writing
        $this->file->write(
            $this->directory . '/' . $filename,
            $this->file->read((new \ReflectionClass($class))->getFileName())
        );

        return $filename;
    }
}