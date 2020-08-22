<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views\Exception;

class CompileException extends EngineException
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
     *
     * @param array $trace
     */
    public function setUserTrace(array $trace): void
    {
        $this->userTrace = $trace;
    }

    /**
     * @return array
     */
    public function getUserTrace(): array
    {
        return $this->userTrace;
    }
}
