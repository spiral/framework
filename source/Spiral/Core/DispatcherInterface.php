<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Debug\SnapshotInterface;

interface DispatcherInterface
{
    /**
     * Start dispatcher.
     */
    public function start();

    /**
     * Every dispatcher should know how to handle exception snapshot provided by spiral core.
     *
     * @param SnapshotInterface $snapshot
     * @return mixed
     */
    public function handleSnapshot(SnapshotInterface $snapshot);
}