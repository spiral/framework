<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Exception\Traits;

use Spiral\Stempler\Parser\Context;

/**
 * To be used on Exceptions.
 */
trait ContextTrait
{
    /** @var Context|null */
    private $context;

    /**
     * @param Context|null    $context
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, Context $context, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->context = $context;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function setLocation(string $filename, int $line): void
    {
        $this->file = $filename;
        $this->line = $line;
    }
}
