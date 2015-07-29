<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Debug;

use Exception;
use Spiral\Core\ContainerInterface;

interface SnapshotInterface
{
    /**
     * Snapshot used to report, render and describe exception in user friendly way. Snapshot may
     * require additional dependencies so it should always be constructed using container.
     *
     * @param Exception          $exception
     * @param ContainerInterface $container
     */
    public function __construct(Exception $exception, ContainerInterface $container);

    /**
     * Associated exception.
     *
     * @return Exception
     */
    public function getException();

    /**
     * Handled exception class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Get short exception name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the file in which the exception occurred.
     *
     * @return string
     */
    public function getFile();

    /**
     * Gets the line in which the exception occurred.
     *
     * @return int
     */
    public function getLine();

    /**
     * Exception trace as array.
     *
     * @return array
     */
    public function getTrace();

    /**
     * Formatted exception message, will include exception class name, original error message and
     * location with fine and line.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Report or store snapshot in known location. Used to store exception information for future
     * analysis.
     */
    public function report();

    /**
     * Get shortened exception description in array form.
     *
     * @return array
     */
    public function describe();

    /**
     * Render exception snapshot to string.
     *
     * @return string
     */
    public function render();
}