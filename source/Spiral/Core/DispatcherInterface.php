<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Debug\SnapshotInterface;

/**
 * Dispatchers are general application flow controllers, system should start them and pass exception
 * or instance of snapshot into them when error happens.
 */
interface DispatcherInterface
{
    /**
     * Start execution from beginning.
     */
    public function start();

    /**
     * Must notify user using given snapshot.
     *
     * @param SnapshotInterface $snapshot
     */
    public function handleSnapshot(SnapshotInterface $snapshot);
}