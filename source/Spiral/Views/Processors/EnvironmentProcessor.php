<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Processors;

use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

/**
 * Mount view environment variables using @{name} pattern.
 */
class EnvironmentProcessor implements ProcessorInterface
{
    /**
     * Variable pattern.
     */
    const DEFAULT_PATTERN = '/@\\{(?P<name>[a-z0-9_\\.\\-]+)(?: *\\| *(?P<default>[^}]+))?}/i';

    /**
     * Pattern for environment variables.
     *
     * @var string
     */
    protected $pattern = '';

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern = null)
    {
        $this->pattern = $pattern ?? static::DEFAULT_PATTERN;
    }

    /**
     * {@inheritdoc}
     */
    public function modify(
        EnvironmentInterface $environment,
        ViewSource $view,
        string $code
    ): string {
        return preg_replace_callback($this->pattern, function ($matches) use ($environment) {
            return $environment->getValue($matches[1]);
        }, $code);
    }
}