<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http;

use Spiral\Core\DispatcherInterface;
use Spiral\Debug\SnapshotInterface;

class HttpDispatcher extends HttpCore implements DispatcherInterface
{
    /**
     * Every dispatcher should know how to handle exception snapshot provided by spiral core.
     *
     * @param SnapshotInterface $snapshot
     * @return mixed
     */
    public function handleSnapshot(SnapshotInterface $snapshot)
    {
        //Nothing
    }
}