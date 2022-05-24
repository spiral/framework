<?php

declare (strict_types=1);

namespace MonorepoBuilder;

use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;

final class ProcessParser implements TagParserInterface
{
    private const COMMAND = ['git', 'tag', '-l', '--sort=committerdate'];

    public function __construct(
        private readonly ProcessRunner $processRunner
    ) {
    }

    /**
     * Returns null, when there are no local tags yet
     */
    public function parse(string $gitDirectory): array
    {
        return \array_filter($this->parseTags($this->processRunner->run(self::COMMAND, $gitDirectory)));
    }

    /**
     * @return string[]
     */
    private function parseTags(string $commandResult): array
    {
        $tags = \trim($commandResult);

        // Remove all "\r" chars in case the CLI env like the Windows OS.
        // Otherwise (ConEmu, git bash, mingw cli, e.g.), leave as is.
        $normalizedTags = \str_replace("\r", '', $tags);

        return \explode("\n", $normalizedTags);
    }
}
