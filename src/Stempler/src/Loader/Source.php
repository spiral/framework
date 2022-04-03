<?php

declare(strict_types=1);

namespace Spiral\Stempler\Loader;

/**
 * Carries information about template content and physical location.
 */
final class Source
{
    public function __construct(
        private readonly string $content,
        private readonly ?string $filename = null
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public static function resolveLine(string $content, int $offset): int
    {
        $line = 0;

        for ($i = 0; $i < $offset; $i++) {
            if (!isset($content[$i])) {
                break;
            }

            if ($content[$i] === "\n") {
                $line++;
            }
        }

        return $line + 1;
    }
}
