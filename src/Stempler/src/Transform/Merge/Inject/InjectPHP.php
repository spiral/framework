<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge\Inject;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Node\Dynamic\Output;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Transform\BlockClaims;
use Spiral\Stempler\Transform\QuotedValue;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Injects block values into PHP source code using marco function.
 */
final class InjectPHP implements VisitorInterface
{
    // php marcos to inject values into
    private const PHP_MACRO_FUNCTION = 'inject';

    private const PHP_MARCO_EXISTS_FUNCTION = 'injected';

    public function __construct(
        private readonly BlockClaims $blocks
    ) {
    }

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        if (
            !$node instanceof PHP
            || (
                !\str_contains($node->content, self::PHP_MACRO_FUNCTION)
                && !\str_contains($node->content, self::PHP_MARCO_EXISTS_FUNCTION)
            )
        ) {
            return null;
        }

        $php = new PHPMixin($node->tokens, self::PHP_MACRO_FUNCTION);
        foreach ($this->blocks->getNames() as $name) {
            $block = $this->blocks->get($name);

            if ($this->isReference($block)) {
                // resolved on later stage
                continue;
            }

            if ($php->has($name)) {
                $php->set($name, $this->trimPHP($this->blocks->claim($name)));
            }
        }

        $node->content = $php->compile();
        $node->tokens = \token_get_all($node->content);

        $exists = new PHPMixin($node->tokens, self::PHP_MARCO_EXISTS_FUNCTION);
        foreach ($this->blocks->getNames() as $name) {
            $block = $this->blocks->get($name);

            if ($this->isReference($block)) {
                // resolved on later stage
                continue;
            }

            if ($exists->has($name)) {
                $exists->set($name, 'true');
            }
        }

        $node->content = $exists->compile();
        $node->tokens = \token_get_all($node->content);

        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }

    private function isReference(mixed $node): bool
    {
        switch (true) {
            case \is_array($node):
                foreach ($node as $child) {
                    if ($this->isReference($child)) {
                        return true;
                    }
                }

                return false;

            case $node instanceof QuotedValue:
                return $this->isReference($node->getValue());

            case $node instanceof Mixin:
                foreach ($node->nodes as $child) {
                    if ($this->isReference($child)) {
                        return true;
                    }
                }

                return false;

            case $node instanceof Block:
                return true;
        }

        return false;
    }

    private function trimPHP(mixed $node): string
    {
        switch (true) {
            case \is_array($node):
                $result = [];
                foreach ($node as $child) {
                    $result[] = $this->trimPHP($child);
                }

                return \implode('.', $result);

            case $node instanceof Mixin:
                $result = [];
                foreach ($node->nodes as $child) {
                    $result[] = $this->trimPHP($child);
                }

                return \implode('.', $result);

            case $node instanceof Raw:
                return $this->exportValue($node);

            case $node instanceof Output:
                return \trim((string) $node->body);

            case $node instanceof PHP:
                return $node->getContext()?->getValue(PHP::ORIGINAL_BODY)
                    ?? (new PHPMixin($node->tokens, self::PHP_MACRO_FUNCTION))->trimBody();

            case $node instanceof QuotedValue:
                return $this->trimPHP($node->trimValue());
        }

        return 'null';
    }

    private function exportValue(Raw $node): string
    {
        $value = $node->content;
        return match (true) {
            \strtolower($value) === 'true' => 'true',
            \strtolower($value) === 'false' => 'false',
            \is_float($value) || \is_numeric($value) => (string) $value,
            default => \var_export($node->content, true),
        };
    }
}
