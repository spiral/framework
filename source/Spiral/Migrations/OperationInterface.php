<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations;

use Spiral\Migrations\Exceptions\OperationException;

/**
 * Represents simple table operation. Operation is a bridge between command and declarative
 * migrations.
 */
interface OperationInterface
{
    /**
     * Database operation related to. Null forces to use default database.
     *
     * @return string|null
     */
    public function getDatabase();

    /**
     * Table operation related to.
     *
     * @return string
     */
    public function getTable(): string;

    /**
     * Execute operation in a given context.
     *
     * @param CapsuleInterface $capsule
     *
     * @throws OperationException
     */
    public function execute(CapsuleInterface $capsule);
}