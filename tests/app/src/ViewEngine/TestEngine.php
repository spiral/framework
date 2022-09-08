<?php

declare(strict_types=1);

namespace Spiral\App\ViewEngine;

use Spiral\Translator\Views\LocaleProcessor;
use Spiral\Views\ContextInterface;
use Spiral\Views\Engine\AbstractEngine;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\Traits\ProcessorTrait;
use Spiral\Views\ViewInterface;
use Spiral\Views\ViewSource;

class TestEngine extends AbstractEngine
{
    use ProcessorTrait;

    protected const EXTENSION = 'custom';

    public function __construct(LocaleProcessor $localeProcessor)
    {
        $this->processors[] = $localeProcessor;
    }

    public function compile(string $path, ContextInterface $context): void
    {
        if ($path === 'custom:error') {
            throw new EngineException('Unable to compile custom:error');
        }
    }

    public function reset(string $path, ContextInterface $context): void
    {
    }

    public function get(string $path, ContextInterface $context): ViewInterface
    {
        $source = new ViewSource($path, 'default', 'locale');
        $source = $source->withCode('Hello [[World]]!');

        return new View($this->process($source, $context));
    }
}
