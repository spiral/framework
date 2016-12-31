<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations;

use Spiral\Migrations\Exceptions\MigrationException;
use Spiral\Migrations\Migration\State;

interface MigrationInterface
{
    /**
     * Version of migration with specific db capsule.
     *
     * @param CapsuleInterface $capsule
     *
     * @return self
     */
    public function withCapsule(CapsuleInterface $capsule): MigrationInterface;

    /**
     * Alter associated migration state (new migration instance to be created).
     *
     * @param State $state
     *
     * @return self
     */
    public function withState(State $state): MigrationInterface;

    /**
     * Get migration state.
     *
     * @return State
     *
     * @throws MigrationException When no state is presented.
     */
    public function getState();

    /**
     * Up migration.
     *
     * @throws MigrationException
     */
    public function up();

    /**
     * Rollback migration.
     *
     * @throws MigrationException
     */
    public function down();
}