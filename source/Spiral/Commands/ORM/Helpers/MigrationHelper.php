<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Commands\ORM\Helpers;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Database\DatabaseManager;
use Spiral\Migrations\Atomizer;
use Spiral\Migrations\Migration;
use Spiral\Migrations\Migrator;
use Spiral\ORM\Schemas\SchemaBuilder;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;

class MigrationHelper
{
    /**
     * @var Migrator
     */
    private $migrator;

    /**
     * @var DatabaseManager
     */
    private $dbal;

    /**
     * @param \Spiral\Migrations\Migrator      $migrator
     * @param \Spiral\Database\DatabaseManager $dbal
     */
    public function __construct(Migrator $migrator, DatabaseManager $dbal)
    {
        $this->migrator = $migrator;
        $this->dbal = $dbal;
    }

    /**
     * @param SchemaBuilder $builder
     *
     * @return string
     */
    public function createMigration(SchemaBuilder $builder): string
    {
        //New migration name
        $migration = 'orm-' . hash('crc32', microtime(true));

        $atomizer = $this->createAtomizer();

        foreach ($builder->getTables() as $table) {
            $atomizer->addTable($table);
        }

        $declaration = new ClassDeclaration(
            'Migration_' . Inflector::classify($migration),
            Migration::class
        );

        $atomizer->declareChanges($declaration->method('up')->setPublic()->getSource());
        $atomizer->revertChanges($declaration->method('down')->setPublic()->getSource());

        //Registering migration
        $this->migrator->getRepository()->registerMigration(
            $migration,
            $declaration->getName(),
            (new FileDeclaration())->addElement($declaration)->render()
        );

        return $migration;
    }

    /**
     * @return Atomizer
     */
    protected function createAtomizer(): Atomizer
    {
        //AliasLookup will help us with correct table names as etc
        return new Atomizer(new Atomizer\MigrationRenderer(
            new Atomizer\AliasLookup($this->dbal)
        ));
    }
}