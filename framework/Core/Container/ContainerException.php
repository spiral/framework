<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Container;

use Exception;
use Spiral\Core\CoreException;

class ContainerException extends CoreException
{
    /**
     * Injection reflection should not be used in application or other code, this is pure spiral core
     * exception raised when system can't correctly resolve dependency injection. Exception build in
     * away to inject real problem location into stack trace.
     *
     * @param Exception         $exception Original exception raised during IoC.
     * @param \ReflectionMethod $method    Context method where injections were defined.
     */
    public function __construct(Exception $exception = null, \ReflectionMethod $method)
    {
        parent::__construct($exception->getMessage(), $exception->code, $exception);

        $this->file = $method->getFileName();
        $this->line = $method->getStartLine();
    }

    /**
     * Build exception stack trace with injected locations of method (or methods) caused error while
     * resolving dependency injection.
     *
     * @return array
     */
    public function injectionTrace()
    {
        //Original exception
        $exception = $this->getPrevious();

        //While nested DI error we have to rebuild whole problem path
        $trace = $exception instanceof ContainerException
            ? $exception->injectionTrace()
            : $exception->getTrace();

        //Known issue with ReflectionException and trace shifting
        if ($exception instanceof \ReflectionException || $exception instanceof ContainerException)
        {
            $trace[0]['file'] = $exception->getFile();
            $trace[0]['line'] = $exception->getLine();
        }

        //Attaching injection location trace at top of call
        array_unshift($trace, $this->getTrace()[0]);

        return $trace;
    }
}