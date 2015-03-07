<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

interface SqlFragmentInterface
{
    /**
     * Get or render SQL statement.
     *
     * @return string
     */
    public function sqlStatement();

    /**
     * __toString
     *
     * @return string
     */
    public function __toString();
}