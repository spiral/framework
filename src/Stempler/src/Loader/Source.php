<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Loader;

/**
 * Carries information about template content and physical location.
 */
final class Source
{
    /** @var string|null */
    private $filename;

    /** @var string */
    private $content;

    /**
     * @param string      $code
     * @param string|null $filename
     */
    public function __construct(string $code, string $filename = null)
    {
        $this->content = $code;
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string $content
     * @param int    $offset
     * @return int
     */
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
