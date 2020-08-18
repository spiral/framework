<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug\Renderer;

use Spiral\Debug\RendererInterface;

abstract class AbstractRenderer implements RendererInterface
{
    /**
     * Container element used to inject dump into, usually pre elemnt with some styling.
     *
     * @var string
     */
    protected $body = '%s';

    /**
     * Default indent string.
     *
     * @var string
     */
    protected $indent = '    ';

    /**
     * @inheritdoc
     */
    public function wrapContent(string $body): string
    {
        return sprintf($this->body, $body);
    }

    /**
     * @inheritdoc
     */
    public function indent(int $level): string
    {
        if ($level == 0) {
            return '';
        }

        return $this->apply(str_repeat($this->indent, $level), 'indent');
    }

    /**
     * @inheritdoc
     */
    public function escapeStrings(): bool
    {
        return true;
    }
}
