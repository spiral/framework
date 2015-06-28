<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Components\Debug\Snapshot;

interface DispatcherInterface
{
    /**
     * Letting dispatcher to control application flow and functionality.
     *
     * @param CoreInterface $core
     */
    public function start(CoreInterface $core);

    /**
     * Every dispatcher should know how to handle exception snapshot provided by Debugger.
     *
     * @param Snapshot $snapshot
     * @return mixed
     */
    public function handleException(Snapshot $snapshot);
}