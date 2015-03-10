<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Debug;

use Spiral\Components\Files\FileManager;
use Spiral\Components\View\View;
use Spiral\Core\Component;
use Spiral\Core\Container\ContainerException;
use Exception;
use Spiral\Core\Dispatcher\ClientException;

class Snapshot extends Component
{
    /**
     * Exception response content is always Exception object handled in Debugger::handleException method.
     *
     * @var \Exception|null
     */
    protected $exception = null;

    /**
     * View name which going to be used to render exception backtrace, backtrace can be either saved to specified file or
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
     * Create new ExceptionResponse object. Object usually generated in Debug::handleException() method and used to show
     * or to store (if specified) backtrace and environment dump or occurred error.
     *
     * @todo include DI for View and FileManager
     * @param Exception $exception
     * @param string    $view      View should be used to render backtrace.
     * @param array     $snapshots Options to render and store error snapshots.
     */
    public function __construct(Exception $exception, $view = '', array $snapshots = array())
    {
        $this->exception = $exception;
        $this->view = $view;

        if (!empty($snapshots['enabled']) && !($exception instanceof ClientException))
        {
            $class = explode('\\', get_class($exception));
            $filename = $snapshots['directory'] . end($class) . '-' . date($snapshots['timeFormat'], time()) . '.html';

            FileManager::getInstance()->write($filename, $this->renderSnapshot(), FileManager::RUNTIME, true);
        }
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
     * Gets the file in which the exception occurred.
     *
     * @return string
     */
    public function getFile()
    {
        return FileManager::getInstance()->normalizePath($this->exception->getFile());
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
     * Formatted exception message, will include exception class name, original error message and location with fine and line.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getClass() . ': ' . $this->exception->getMessage() . ' in ' . $this->getFile() . ' at line ' . $this->getLine();
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

        return $this->snapshot = View::getInstance()->render($this->view, array('exception' => $this));
    }
}