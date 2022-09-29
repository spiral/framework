<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Import;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;
use Spiral\Stempler\Transform\Context\ImportContext;

/**
 * Elements which are declared as root level blocks.
 */
final class Bundle implements ImportInterface
{
    use ContextTrait;

    private ?Template $template = null;

    public function __construct(
        private string $path,
        private ?string $prefix = null,
        Context $context = null
    ) {
        $this->context = $context;
    }

    public function resolve(Builder $builder, string $name): ?Template
    {
        if ($this->template === null) {
            $this->template = $builder->load($this->path);
        }

        $path = $name;
        if ($this->prefix !== null) {
            $path = \substr($path, \strlen($this->prefix) + 1);
        }

        /** @var ImportInterface $import */
        foreach ($this->template->getAttribute(ImportContext::class, []) as $import) {
            $tpl = $import->resolve($builder, $path);
            if ($tpl !== null) {
                return $tpl;
            }
        }

        return null;
    }
}
