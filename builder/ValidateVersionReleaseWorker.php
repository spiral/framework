<?php

declare(strict_types=1);

namespace MonorepoBuilder;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Exception\Git\InvalidGitVersionException;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

final class ValidateVersionReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private readonly TagParserInterface $parser,
        private ?string $gitDirectory = null,
    ) {
        if ($gitDirectory === null) {
            $this->gitDirectory = \dirname(__DIR__);
        }
    }

    public function getDescription(Version $version): string
    {
        return \sprintf(
            'Checking if the version [%s] is greater than the latest released version.',
            $version->getVersionString(),
        );
    }

    public function work(Version $version): void
    {
        $mostRecentVersion = $this->findMostRecentVersion($version);

        // no tag yet
        if ($mostRecentVersion === null) {
            return;
        }

        // validation
        $mostRecentVersion = new Version(\strtolower($mostRecentVersion));
        if ($version->isGreaterThan($mostRecentVersion)) {
            return;
        }

        throw new InvalidGitVersionException(\sprintf(
            'Provided version "%s" must be greater than the last one: "%s"',
            $version->getVersionString(),
            $mostRecentVersion->getVersionString(),
        ));
    }

    private function findMostRecentVersion(Version $version): ?string
    {
        $tags = [];
        foreach ($this->parser->parse($this->gitDirectory) as $tag) {
            $tag = new Version(\strtolower($tag));

            // all previous major versions
            if ($version->getMajor()->getValue() > $tag->getMajor()->getValue()) {
                $tags[] = $tag;
            }

            // all minor versions up to the requested in the requested major version
            if ($version->getMajor()->getValue() === $tag->getMajor()->getValue()) {
                if ($version->getMinor()->getValue() >= $tag->getMinor()->getValue()) {
                    $tags[] = $tag;
                }
            }
        }

        if ($tags === []) {
            return null;
        }

        \usort($tags, static fn(Version $a, Version $b) => $a->isGreaterThan($b) ? -1 : 1);

        return $tags[0]->getVersionString();
    }
}
