<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations;

use Spiral\Migrations\Exceptions\RepositoryException;

interface RepositoryInterface
{
    /**
     * Get every available migration from repository in a valid order, Meta property of migration
     * must be filled with information except execution status and execution time.
     *
     * Attention, Capsule must be set for migration before executing it.
     *
     * @return MigrationInterface[]
     *
     * @throws RepositoryException
     */
    public function getMigrations(): array;

    /**
     * Register new migration using given migration file body (must be valid filename), every
     * migration must have unique class name
     *
     * @param string $name
     * @param string $class
     * @param string $body When body is null repository will try to copy content from a specific
     *                     class filename.
     *
     * @return string Migration filename.
     *
     * @throws RepositoryException
     */
    public function registerMigration(string $name, string $class, string $body = null): string;
}