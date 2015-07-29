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
use Spiral\Core\ContainerInterface;
use Spiral\Files\FilesInterface;
use Spiral\Views\ViewsInterface;

class Snapshot extends Component implements SnapshotInterface
{
    /**
     * Message format.
     */
    const MESSAGE = "{exception}: {message} in {file} at line {line}";

    /**
     * Part of debug configuration.
     */
    const CONFIG = 'snapshots';

    /**
     * Associated exception.
     *
     * @var \Exception
     */
    protected $exception = null;

    /**
     * Used for logging.
     *
     * @var Debugger
     */
    protected $debugger = null;

    /**
     * To store snapshot at hard drive.
     *
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * To render snapshot.
     *
     * @var ViewsInterface
     */
    protected $views = null;

    /**
     * Snapshot specific configuration.
     *
     * @var array
     */
    protected $config = [];

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
     * @param Exception          $exception
     * @param ContainerInterface $container
     * @param Debugger           $debugger
     * @param FilesInterface     $files
     * @param ViewsInterface     $views
     */
    public function __construct(
        Exception $exception,
        ContainerInterface $container,
        Debugger $debugger = null,
        FilesInterface $files = null,
        ViewsInterface $views = null
    )
    {
        $this->exception = $exception;
        $this->debugger = !empty($debugger) ? $debugger : $container->get(Debugger::class);

        $this->files = !empty($files) ? $files : $container->get(FilesInterface::class);
        $this->views = !empty($views) ? $views : $container->get(ViewsInterface::class);

        //Snapshots configuration
        $this->config = $debugger->getConfig()[static::CONFIG];
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

    /**
     * Report or store snapshot in known location. Used to store exception information for future
     * analysis.
     */
    public function report()
    {
        $this->debugger->logger()->error($this->getMessage());

        if (!$this->config['reporting']['enabled'])
        {
            //No need to record anything
            return;
        }

        $filename = interpolate($this->config['reporting']['filename'], [
            'date'      => date($this->config['reporting']['dateFormat'], time()),
            'exception' => $this->getName()
        ]);

        //Writing to hard drive
        $this->files->write(
            $this->config['reporting']['directory'] . '/' . $filename,
            $this->render(),
            FilesInterface::RUNTIME,
            true
        );
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
        if (!empty($this->renderCache))
        {
            return $this->renderCache;
        }

        return $this->renderCache = $this->views->render($this->config['view'], [
            'snapshot' => $this
        ]);
    }
}