<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Engines\Traits;

use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ViewSource;

/**
 * Provides support to pre-process view source before sending it to engine. Processing always
 * environment specific.
 */
trait ProcessorsTrait
{
    /**
     * @var \Spiral\Views\ProcessorInterface[]
     */
    private $processors;

    /**
     * Pre-process view source.
     *
     * @param EnvironmentInterface $environment
     * @param ViewSource           $source
     *
     * @return ViewSource
     */
    private function processSource(
        EnvironmentInterface $environment,
        ViewSource $source
    ): ViewSource {
        foreach ($this->processors as $processor) {
            $source = $source->withCode(
                $processor->modify($environment, $source, $source->getCode())
            );
        }

        return $source;
    }
}