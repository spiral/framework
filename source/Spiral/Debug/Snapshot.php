<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Debug;

use Spiral\Components\View\ViewManager;
use Spiral\Core\Component;
use Spiral\Core\Container\ContainerException;
use Exception;

class Snapshot extends Component
{
    /**
     * Exception response content is always Exception object handled in Debugger::handleException
     * method.
     *
     * @var \Exception|null
     */
    protected $exception = null;

    /**
     * ViewManager used to render snapshots.
     *
     * @invisible
     * @var ViewManager
     */
    protected $viewManager = null;

    /**
     * View name which going to be used to render exception backtrace, backtrace can be either saved
     * to specified file or
     * send to client.
     *
     * @var string
     */
    protected $view = '';

    /**
     * Rendered backtrace view, can be used in to save into file, send by email or show to client.
     *
     * @var string
     */
    protected $snapshot = '';

    /**
     * Create new ExceptionResponse object. Object usually generated in Debug::handleException()
     * method and used to show or to store (if specified) backtrace and environment dump or occurred
     * error.
     *
     * @param Exception   $exception
     * @param ViewManager $viewManager
     * @param string      $view View should be used to render backtrace.
     */
    public function __construct(
        Exception $exception,
        ViewManager $viewManager,
        $view = ''
    )
    {
        $this->exception = $exception;
        $this->viewManager = $viewManager;
        $this->view = $view;
    }

    /**
     * Handled exception object.
     *
     * @return Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Get short exception name.
     *
     * @return string
     */
    public function getName()
    {
        $reflection = new \ReflectionObject($this->exception);

        return $reflection->getShortName();
    }

    /**
     * Gets the file in which the exception occurred.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->exception->getFile();
    }

    /**
     * Gets the line in which the exception occurred.
     *
     * @return int
     */
    public function getLine()
    {
        return $this->exception->getLine();
    }

    /**
     * Exception trace as array.
     *
     * @return array
     */
    public function getTrace()
    {
        if ($this->exception instanceof ContainerException)
        {
            //Corrected injection trace
            return $this->exception->injectionTrace();
        }

        return $this->exception->getTrace();
    }

    /**
     * Handled exception class name.
     *
     * @return string
     */
    public function getClass()
    {
        if ($this->exception instanceof ContainerException)
        {
            //Corrected injection trace
            return get_class($this->exception->getPrevious());
        }

        return get_class($this->exception);
    }

    /**
     * Formatted exception message, will include exception class name, original error message and
     * location with fine and line.
     *
     * @return string
     */
    public function getMessage()
    {
        return interpolate("{exception}: {message} in {file} at line {line}", [
            'exception' => $this->getClass(),
            'message'   => $this->exception->getMessage(),
            'file'      => $this->getFile(),
            'line'      => $this->getLine()
        ]);
    }

    /**
     * Render exception backtrace and environment snapshot using specified view name.
     *
     * @return string
     */
    public function renderSnapshot()
    {
        if ($this->snapshot || !$this->view)
        {
            return $this->snapshot;
        }

        return $this->snapshot = $this->viewManager->render($this->view, [
            'exception' => $this
        ]);
    }

    /**
     * Get shortened exception description. Usually used to send data over ajax.
     *
     * @return array
     */
    public function packException()
    {
        return [
            'error'    => $this->getMessage(),
            'location' => [
                'file' => $this->getFile(),
                'line' => $this->getLine()
            ],
            'trace'    => $this->getTrace()
        ];
    }

    /**
     * Render snapshot to client.
     *
     * @return string
     */
    public function __toString()
    {
        if (PHP_SAPI === 'cli')
        {
            return (string)$this->exception;
        }

        return $this->renderSnapshot();
    }
}