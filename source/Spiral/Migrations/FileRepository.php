<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Core\FactoryInterface;
use Spiral\Files\FilesInterface;
use Spiral\Migrations\Configs\MigrationsConfig;
use Spiral\Migrations\Exceptions\RepositoryException;
use Spiral\Migrations\Migration\State;
use Spiral\Tokenizer\TokenizerInterface;

/**
 * Stores migrations as files.
 */
class FileRepository implements RepositoryInterface
{
    /**
     * Migrations file name format. This format will be used when requesting new migration filename.
     */
    const FILENAME_FORMAT = '{timestamp}_{chunk}_{name}.php';

    /**
     * Timestamp format for files.
     */
    const TIMESTAMP_FORMAT = 'Ymd.His';

    /**
     * @var MigrationsConfig
     */
    private $config = null;

    /**
     * Required when multiple migrations added at once.
     *
     * @var int
     */
    private $chunkID = 0;

    /**
     * @invisible
     * @var TokenizerInterface
     */
    protected $tokenizer = null;

    /**
     * @invisible
     * @var FactoryInterface
     */
    protected $factory = null;

    /**
     * @invisible
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param MigrationsConfig   $config
     * @param TokenizerInterface $tokenizer
     * @param FilesInterface     $files
     * @param FactoryInterface   $factory
     */
    public function __construct(
        MigrationsConfig $config,
        TokenizerInterface $tokenizer,
        FilesInterface $files,
        FactoryInterface $factory
    ) {
        $this->config = $config;

        $this->tokenizer = $tokenizer;
        $this->files = $files;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrations(): array
    {
        $migrations = [];

        foreach ($this->getFiles() as $definition) {
            if (!class_exists($definition['class'], false)) {
                require_once($definition['filename']);
            }

            if (!$definition['created'] instanceof \DateTime) {
                throw new RepositoryException(
                    "Invalid migration filename '{$definition['filename']}'"
                );
            }

            /**
             * @var MigrationInterface $migration
             */
            $migration = $this->factory->make($definition['class']);

            $migrations[$definition['filename']] = $migration->withState(
                new State($definition['name'], $definition['created'])
            );
        }

        return $migrations;
    }

    /**
     * {@inheritdoc}
     */
    public function registerMigration(string $name, string $class, string $body = null): string
    {
        if (empty($body) && !class_exists($class)) {
            throw new RepositoryException(
                "Unable to register migration '{$class}', representing class does not exists"
            );
        }

        foreach ($this->getMigrations() as $migration) {
            if (get_class($migration) == $class) {
                throw new RepositoryException(
                    "Unable to register migration '{$class}', migration already exists"
                );
            }

            if ($migration->getState()->getName() == $name) {
                throw new RepositoryException(
                    "Unable to register migration '{$name}', migration under same name already exists"
                );
            }
        }

        if (empty($body)) {
            //Let's read body from a given class filename
            $body = $this->files->read((new \ReflectionClass($class))->getFileName());
        }

        //Copying
        $this->files->write(
            $filename = $this->createFilename($name),
            $body,
            FilesInterface::READONLY
        );

        return basename($filename);
    }

    /**
     * Internal method to fetch all migration filenames.
     */
    private function getFiles(): \Generator
    {
        foreach ($this->files->getFiles($this->config['directory'], '*.php') as $filename) {
            $reflection = $this->tokenizer->fileReflection($filename);

            $definition = explode('_', basename($filename));

            yield [
                'filename' => $filename,
                'class'    => $reflection->getClasses()[0],
                'created'  => \DateTime::createFromFormat(self::TIMESTAMP_FORMAT, $definition[0]),
                'name'     => str_replace('.php', '', join('_', array_slice($definition, 2)))
            ];
        }
    }

    /**
     * Request new migration filename based on user input and current timestamp.
     *
     * @param string $name
     *
     * @return string
     */
    private function createFilename(string $name): string
    {
        $name = Inflector::tableize($name);

        $filename = \Spiral\interpolate(self::FILENAME_FORMAT, [
            'timestamp' => date(self::TIMESTAMP_FORMAT),
            'chunk'     => $this->chunkID++,
            'name'      => $name
        ]);

        return $this->files->normalizePath(
            $this->config->getDirectory() . FilesInterface::SEPARATOR . $filename
        );
    }
}