<?php

declare(strict_types=1);

namespace Spiral\Debug\Renderer;

use Spiral\Debug\RendererInterface;

/**
 * @deprecated since v2.13. Will be removed in v3.0
 */
abstract class AbstractRenderer implements RendererInterface
{
    /**
     * Container element used to inject dump into, usually pre elemnt with some styling.
     */
    protected string $body = '%s';

    /**
     * Default indent string.
     */
    protected string $indent = '    ';

    public function wrapContent(string $body): string
    {
        return \sprintf($this->body, $body);
    }

    public function indent(int $level): string
    {
        if ($level === 0) {
            return '';
        }

        return $this->apply(\str_repeat($this->indent, $level), 'indent');
    }

    public function escapeStrings(): bool
    {
        return true;
    }
}
