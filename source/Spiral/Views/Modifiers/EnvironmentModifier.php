<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Modifiers;

use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ModifierInterface;

/**
 * Mount view environment variables using @{name} pattern.
 */
class EnvironmentModifier implements ModifierInterface
{
    /**
     * Variable pattern.
     */
    const DEFAULT_PATTERN = '/@\\{(?P<name>[a-z0-9_\\.\\-]+)(?: *\\| *(?P<default>[^}]+))?}/i';

    /**
     * @invisible
     * @var EnvironmentInterface
     */
    protected $environment = null;

    /**
     * Pattern for environment variables.
     *
     * @var string
     */
    protected $pattern = '';

    /**
     * All modifiers should be requested using container (you can add more dependencies).
     *
     * @param EnvironmentInterface $environment
     * @param string $pattern
     */
    public function __construct(EnvironmentInterface $environment, $pattern = '')
    {
        $this->environment = $environment;
        $this->pattern = !empty($pattern) ? $pattern : static::DEFAULT_PATTERN;
    }

    /**
     * Modify given source.
     *
     * @param string $source Source.
     * @param string $namespace View namespace.
     * @param string $name View name (no extension included).
     * @return mixed
     */
    public function modify($source, $namespace, $name)
    {
        return preg_replace_callback($this->pattern, function ($matches) {
            return $this->environment->getValue($matches[1]);
        }, $source);
    }
}