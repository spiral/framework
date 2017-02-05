<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Exceptions;

/**
 * Exception while rendering.
 */
class RenderException extends ViewsException
{
    /**
     * {@inheritdoc}
     */
    public function __construct(\Exception $previous = null)
    {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
        $this->file = $previous->file;
        $this->line = $previous->line;
    }
}