<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

interface SqlIdentifierInterface
{
    /**
     * Get identifier content.
     *
     * @return string
     */
    public function getIdentifier();
}