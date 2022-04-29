<?php

declare(strict_types=1);

namespace Spiral\Debug\Renderer;

use Spiral\Debug\RendererInterface;

/**
 * HTML renderer with switchable color schemas.
 *
 * @deprecated since v2.13. Will be removed in v3.0
 */
final class HtmlRenderer implements RendererInterface
{
    /**
     * Default coloring schema.
     */
    final public const DEFAULT = [
        'body'     => '<pre style="background-color: white; font-family: monospace;">%s</pre>',
        'element'  => '<span style="%s;">%s</span>',
        'indent'   => '&middot;    ',
        'common'   => 'color: black',
        'name'     => 'color: black',
        'dynamic'  => 'color: purple;',
        'maxLevel' => 'color: #ff9900',
        'syntax'   => [
            'common' => 'color: #666',
            '['      => 'color: black',
            ']'      => 'color: black',
            '('      => 'color: black',
            ')'      => 'color: black',
        ],
        'value'    => [
            'string'  => 'color: green',
            'integer' => 'color: red',
            'double'  => 'color: red',
            'boolean' => 'color: purple; font-weight: bold;',
        ],
        'type'     => [
            'common'   => 'color: #666',
            'object'   => 'color: #333',
            'array'    => 'color: #333',
            'null'     => 'color: #666; font-weight: bold;',
            'resource' => 'color: #666; font-weight: bold;',
        ],
        'access'   => [
            'common'    => 'color: #666',
            'public'    => 'color: #8dc17d',
            'private'   => 'color: #c18c7d',
            'protected' => 'color: #7d95c1',
        ],
    ];

    /**
     * Inverted coloring schema.
     */
    final public const INVERTED = [
        'body'     => '<pre style="background-color: #232323; font-family: Monospace;">%s</pre>',
        'element'  => '<span style="%s;">%s</span>',
        'indent'   => '&middot;    ',
        'common'   => 'color: #E6E1DC',
        'name'     => 'color: #E6E1DC',
        'dynamic'  => 'color: #7d95c1;',
        'maxLevel' => 'color: #ff9900',
        'syntax'   => [
            'common' => 'color: gray',
            '['      => 'color: #E6E1DC',
            ']'      => 'color: #E6E1DC',
            '('      => 'color: #E6E1DC',
            ')'      => 'color: #E6E1DC',
        ],
        'value'    => [
            'string'  => 'color: #A5C261',
            'integer' => 'color: #A5C261',
            'double'  => 'color: #A5C261',
            'boolean' => 'color: #C26230; font-weight: bold;',
        ],
        'type'     => [
            'common'   => 'color: #E6E1DC',
            'object'   => 'color: #E6E1DC',
            'array'    => 'color: #E6E1DC',
            'null'     => 'color: #C26230; font-weight: bold',
            'resource' => 'color: #C26230; font-weight: bold',
        ],
        'access'   => [
            'common'    => 'color: #666',
            'public'    => 'color: #8dc17d',
            'private'   => 'color: #c18c7d',
            'protected' => 'color: #7d95c1',
        ],
    ];

    /**
     * @param array $style Set of styles associated with different dumping properties.
     */
    public function __construct(
        protected array $style = self::DEFAULT
    ) {
    }

    public function apply(mixed $element, string $type, string $context = ''): string
    {
        if (!empty($style = $this->getStyle($type, $context))) {
            return \sprintf($this->style['element'], $style, $element);
        }

        return $element;
    }

    public function wrapContent(string $body): string
    {
        return \sprintf($this->style['body'], $body);
    }

    public function indent(int $level): string
    {
        if ($level === 0) {
            return '';
        }

        return $this->apply(\str_repeat($this->style['indent'], $level), 'indent');
    }

    public function escapeStrings(): bool
    {
        return true;
    }

    /**
     * Get valid stype based on type and context/.
     */
    private function getStyle(string $type, string $context): string
    {
        return match (true) {
            isset($this->style[$type][$context]) => $this->style[$type][$context],
            isset($this->style[$type]['common']) => $this->style[$type]['common'],
            isset($this->style[$type]) && \is_string($this->style[$type]) => $this->style[$type],
            default => $this->style['common']
        };
    }
}
