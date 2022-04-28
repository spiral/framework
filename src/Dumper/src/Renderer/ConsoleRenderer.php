<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug\Renderer;

use Codedungeon\PHPCliColors\Color;

/**
 * Colorful styling for CLI dumps.
 *
 * @deprecated since v2.13. Will be removed in v3.0
 */
final class ConsoleRenderer extends AbstractRenderer
{
    /**
     * Every dumped element is wrapped using this pattern.
     *
     * @var string
     */
    protected $element = '%s%s' . Color::RESET;

    /**
     * Set of styles associated with different dumping properties.
     *
     * @var array
     */
    protected $styles = [
        'common'   => Color::BOLD_WHITE,
        'name'     => Color::LIGHT_WHITE,
        'dynamic'  => Color::PURPLE,
        'maxLevel' => Color::RED,
        'syntax'   => [
            'common' => Color::WHITE,
            '['      => Color::BOLD_WHITE,
            ']'      => Color::BOLD_WHITE,
            '('      => Color::BOLD_WHITE,
            ')'      => Color::BOLD_WHITE,
        ],
        'value'    => [
            'string'  => Color::GREEN,
            'integer' => Color::LIGHT_CYAN,
            'double'  => Color::LIGHT_CYAN,
            'boolean' => Color::LIGHT_PURPLE,
        ],
        'type'     => [
            'common'   => Color::WHITE,
            'object'   => Color::LIGHT_BLUE,
            'null'     => Color::LIGHT_PURPLE,
            'resource' => Color::PURPLE,
        ],
        'access'   => Color::GRAY,
    ];

    /**
     * @inheritdoc
     */
    public function apply($element, string $type, string $context = ''): string
    {
        if (!empty($style = $this->getStyle($type, $context))) {
            return sprintf($this->element, $style, $element);
        }

        return $element;
    }

    /**
     * @inheritdoc
     */
    public function escapeStrings(): bool
    {
        return false;
    }

    /**
     * Get valid style based on type and context/.
     *
     *
     */
    private function getStyle(string $type, string $context): string
    {
        if (isset($this->styles[$type][$context])) {
            return $this->styles[$type][$context];
        }

        if (isset($this->styles[$type]['common'])) {
            return $this->styles[$type]['common'];
        }

        if (isset($this->styles[$type]) && is_string($this->styles[$type])) {
            return $this->styles[$type];
        }

        return $this->styles['common'];
    }
}
