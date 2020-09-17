<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Spiral\Http\Middleware\ErrorHandlerMiddleware;

use Spiral\Boot\EnvironmentInterface;

class EnvSuppressErrors implements SuppressErrorsInterface
{
    /** @var EnvironmentInterface */
    private $environment;

    /**
     * @param EnvironmentInterface $environment
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @inheritdoc
     */
    public function suppressed(): bool
    {
        return !$this->environment->get('DEBUG', false);
    }
}
