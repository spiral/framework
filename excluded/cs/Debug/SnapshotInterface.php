<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Debug;

/**
 * Exception wrapper used to describe and show exception information in user friendly way.
 */
interface SnapshotInterface
{
    /**
     * Associated exception.
     *
     * @return \Throwable
     */
    public function getException();

    /**
     * Must return formatted exception message including exception class, location and etc.
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
     * Render snapshot information into string or html.
     *
     * @return string
     */
    public function render();
}