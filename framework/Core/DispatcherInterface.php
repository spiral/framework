<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Components\Debug\ExceptionSnapshot;

interface DispatcherInterface
{
    /**
     * Letting dispatcher to control application flow and functionality.
     *
     * @param Core $core
     */
    public function start(Core $core);

    /**
     * Every dispatcher should know how to handle exception snapshot provided by Debugger.
     *
     * @param ExceptionSnapshot $snapshot
     * @return mixed
     */
    public function handleException(ExceptionSnapshot $snapshot);
}