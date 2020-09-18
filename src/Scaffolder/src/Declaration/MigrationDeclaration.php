<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Migrations\Migration;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;

/**
 * Migration declaration
 */
class MigrationDeclaration extends ClassDeclaration implements DependedInterface
{
    /**
     * @param string $name
     * @param string $comment
     */
    public function __construct(string $name, string $comment = '')
    {
        parent::__construct($name, 'Migration', [], $comment);

        $this->declareStructure();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [Migration::class => null];
    }

    /**
     * Declare table creation with specific set of columns
     *
     * @param string $table
     * @param array  $columns
     */
    public function declareCreation(string $table, array $columns): void
    {
        $source = $this->method('up')->getSource();

        $source->addLine("\$this->table('{$table}')");
        foreach ($columns as $name => $type) {
            $source->addLine("    ->addColumn('{$name}', '{$type}')");
        }

        $source->addLine('    ->create();');

        $this->method('down')->getSource()->addLine("\$this->table('{$table}')->drop();");
    }

    /**
     * Declare default __invoke method body.
     */
    private function declareStructure(): void
    {
        $up = $this->method('up')->setPublic()->setReturn('void');
        $down = $this->method('down')->setPublic()->setReturn('void');

        $up->setComment('Create tables, add columns or insert data here');
        $down->setComment('Drop created, columns and etc here');
    }
}
