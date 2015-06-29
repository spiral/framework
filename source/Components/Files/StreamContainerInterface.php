<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Files;

use Psr\Http\Message\StreamInterface;

interface StreamContainerInterface
{
    /**
     * Get associated stream.
     *
     * @return StreamInterface
     */
    public function getStream();
}