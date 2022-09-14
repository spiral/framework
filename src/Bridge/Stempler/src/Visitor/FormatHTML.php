<?php

declare(strict_types=1);

namespace Spiral\Stempler\Visitor;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Set proper indents for all HTML tags.
 */
final class FormatHTML implements VisitorInterface
{
    // default indent
    private const INDENT = '  ';

    private const EXCLUDE = ['pre', 'textarea'];

    // indent exceptions
    private const BETWEEN_TAGS = 0;
    private const BEFORE_PHP   = 1;
    private const BEFORE_CLOSE = 2;

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        if (!$node instanceof Template && !$node instanceof Block && !$node instanceof Tag) {
            return null;
        }

        if ($node instanceof Tag && \in_array($node->name, self::EXCLUDE)) {
            // raw nodes
            return null;
        }

        $level = $this->getLevel($ctx);
        if ($level === null) {
            // not available in some contexts
            return null;
        }

        foreach ($node->nodes as $i => $child) {
            if (!$child instanceof Raw) {
                continue;
            }

            $position = self::BETWEEN_TAGS;
            if (!isset($node->nodes[$i + 1])) {
                $position = self::BEFORE_CLOSE;
            } elseif ($node->nodes[$i + 1] instanceof PHP) {
                $position = self::BEFORE_PHP;
            }

            $child->content = $this->indentContent(
                $this->normalizeEndings((string)$child->content, false),
                $level,
                $position
            );
        }

        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }

    private function indentContent(string $content, int $level, int $position = self::BETWEEN_TAGS): string
    {
        if (!\str_contains($content, "\n")) {
            // no need to do anything
            return $content;
        }

        // we have to apply special rules to the first and the last lines
        $lines = \explode("\n", $content);

        foreach ($lines as $i => $line) {
            if (\trim($line) === '' && $i !== 0) {
                unset($lines[$i]);
            }
        }

        $lines = \array_values($lines);
        if ($lines === []) {
            $lines[] = '';
        }

        $result = '';
        foreach ($lines as $i => $line) {
            if (\trim($line) !== '') {
                $line = $i === 0 ? \rtrim($line) : \trim($line);
            }

            if ($i !== (\count($lines) - 1)) {
                $result .= $line . "\n" . \str_repeat(self::INDENT, $level);
                continue;
            }

            // working with last line
            if ($position === self::BEFORE_PHP) {
                $result .= $line . "\n";
                break;
            }

            if ($position === self::BEFORE_CLOSE) {
                $result .= $line . "\n" . \str_repeat(self::INDENT, max($level - 1, 0));
                break;
            }

            $result .= $line . "\n" . \str_repeat(self::INDENT, $level);
        }

        return $result;
    }

    private function getLevel(VisitorContext $ctx): ?int
    {
        $level = 0;
        foreach ($ctx->getScope() as $node) {
            if ($node instanceof Attr) {
                return null;
            }

            if ($node instanceof Block || $node instanceof Template) {
                continue;
            }

            $level++;
        }

        return $level;
    }

    /**
     * Normalize string endings to avoid EOL problem. Replace \n\r and multiply new lines with
     * single \n.
     *
     * @param string $string       String to be normalized.
     * @param bool   $joinMultiple Join multiple new lines into one.
     */
    private function normalizeEndings(string $string, bool $joinMultiple = true): string
    {
        if (!$joinMultiple) {
            return \str_replace("\r\n", "\n", $string);
        }

        return \preg_replace('/[\n\r]+/', "\n", $string);
    }
}
