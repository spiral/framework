<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views\Exception;

class RenderException extends ViewException
{
    /** @var array */
    private $userTrace = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
        $this->file = $previous->getFile();
        $this->line = $previous->getLine();
    }

    /**
     * Set user trace pointing to the location of error in view templates.
     */
    public function setUserTrace(array $trace): void
    {
        $this->userTrace = $trace;
    }

    public function getUserTrace(): array
    {
        return $this->userTrace;
    }
}
