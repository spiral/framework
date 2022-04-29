<?php

declare(strict_types=1);

namespace Spiral\Views\Traits;

use Spiral\Views\ContextInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

trait ProcessorTrait
{
    /** @var ProcessorInterface[] */
    private array $processors = [];

    /**
     * Process given view source using set of associated processors.
     */
    private function process(ViewSource $source, ContextInterface $context): ViewSource
    {
        foreach ($this->processors as $processor) {
            $source = $processor->process($source, $context);
        }

        return $source;
    }
}
