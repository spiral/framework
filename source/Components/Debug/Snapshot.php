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
use Spiral\Components\View\ViewManager;
use Spiral\Core\Component;
use Spiral\Core\Container\ContainerException;
use Exception;
use Spiral\Core\Core;
use Spiral\Core\Dispatcher\ClientException;

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
     * FileManager used to save snaphots.
     *
     * @var FileManager
     */
    protected $fileManager = null;

    /**
     * ViewManager used to render snapshots.
     *
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
     * Filename where snapshot is stored into.
     *
     * @var string|null
     */
    protected $snapshotFilename = null;

    /**
     * Create new ExceptionResponse object. Object usually generated in Debug::handleException()
     * method and used to show or to store (if specified) backtrace and environment dump or occurred
     * error.
     *
     * @param Exception   $exception
     * @param FileManager $fileManager
     * @param ViewManager $viewManager
     * @param string      $view   View should be used to render backtrace.
     * @param array       $config Options to render and store error snapshots.
     */
    public function __construct(
        Exception $exception,
        FileManager $fileManager,
        ViewManager $viewManager,
        $view = '',
        array $config = array()
    )
    {
        $this->exception = $exception;

        $this->fileManager = $fileManager;
        $this->viewManager = $viewManager;

        $this->view = $view;

        if (!empty($config['enabled']) && !($exception instanceof ClientException))
        {
            $reflection = new \ReflectionObject($exception);
            $this->snapshotFilename = $this->getFilename($config, $reflection->getShortName());

            $this->fileManager->write(
                $this->snapshotFilename,
                $this->renderSnapshot(),
                FileManager::RUNTIME,
                true
            );
        }
    }

    /**
     * Create snapshot filename based on provided config.
     *
     * @param array  $config
     * @param string $shortName Exception short name.
     * @return string
     */
    protected function getFilename(array $config = array(), $shortName)
    {
        $name = date($config['timeFormat'], time()) . '-' . $shortName . '.html';

        return $config['directory'] . '/' . $name;
    }

    /**
     * Get location where snapshot is stored into.
     *
     * @return string|null
     */
    public function getSnapshotFilename()
    {
        return $this->snapshotFilename;
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
        return $this->fileManager->normalizePath($this->exception->getFile());
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
        return $this->getClass() . ': ' . $this->exception->getMessage()
        . ' in ' . $this->getFile() . ' at line ' . $this->getLine();
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

        return $this->snapshot = $this->viewManager->render($this->view, array(
            'exception' => $this
        ));
    }

    /**
     * Get shortened exception description. Usually used to send data over ajax.
     *
     * @return array
     */
    public function packException()
    {
        return array(
            'error'    => $this->getMessage(),
            'location' => array(
                'file' => $this->getFile(),
                'line' => $this->getLine()
            ),
            'trace'    => $this->getTrace()
        );
    }

    /**
     * Render snapshot to client.
     *
     * @return string
     */
    public function __toString()
    {
        if (Core::isConsole())
        {
            return (string)$this->exception;
        }

        return $this->renderSnapshot();
    }
}