<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Application;

use Spiral\Debug\Snapshot;

interface DispatcherInterface
{
    /**
     * Start dispatcher.
     */
    public function start();

    /**
     * Every dispatcher should know how to handle exception snapshot provided by spiral core.
     *
     * @param Snapshot $snapshot
     * @return mixed
     */
    public function handleException(Snapshot $snapshot);
}