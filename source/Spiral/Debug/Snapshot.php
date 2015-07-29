<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Debug;

use Spiral\Core\Component;
use Exception;

class Snapshot extends Component implements SnapshotInterface
{
    /**
     * Message format.
     */
    const MESSAGE = "{exception}: {message} in {file} at line {line}";

    /**
     * Associated exception.
     *
     * @var \Exception
     */
    protected $exception = null;

    /**
     * Rendered backtrace view, can be used in to save into file, send by email or show to client.
     *
     * @var string
     */
    protected $renderCache = '';

    /**
     * Snapshot used to report, render and describe exception in user friendly way. Snapshot may
     * require additional dependencies so it should always be constructed using container.
     *
     * @param Exception $exception
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
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
     * Handled exception class name.
     *
     * @return string
     */
    public function getClass()
    {
        return get_class($this->exception);
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
        return $this->exception->getTrace();
    }

    /**
     * Formatted exception message, will include exception class name, original error message and
     * location with fine and line.
     *
     * @return string
     */
    public function getMessage()
    {
        return interpolate(static::MESSAGE, [
            'exception' => $this->getClass(),
            'message'   => $this->exception->getMessage(),
            'file'      => $this->getFile(),
            'line'      => $this->getLine()
        ]);
    }

    public function report()
    {
    }

    /**
     * Get shortened exception description. Usually used to send data over ajax.
     *
     * @return array
     */
    public function describe()
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
     * Render exception snapshot to string.
     *
     * @return string
     */
    public function render()
    {
    }
}