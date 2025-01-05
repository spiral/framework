<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\MonorepoBuilder;

use MonorepoBuilder\TagParserInterface;
use MonorepoBuilder\ValidateVersionReleaseWorker;
use PharIo\Version\Version;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// Monorepo builder doesn't have autoloading
require_once \dirname(__DIR__, 3) . '/vendor/symplify/monorepo-builder/packages/Release/Contract/ReleaseWorker/ReleaseWorkerInterface.php';

final class ValidateVersionReleaseWorkerTest extends TestCase
{
    private const TAGS = [
        '0.1.0',
        '0.1.2',
        '0.1.11',
        '1.0.0',
        '1.0.1',
        '1.1.0',
        '1.1.3',
        '1.2.10',
        '2.0.0',
        '2.0.2',
        '2.1.0',
        '2.1.1',
        '3.0.0',
        '3.0.1',
        '3.1.0'
    ];

    private ValidateVersionReleaseWorker $worker;

    protected function setUp(): void
    {
        $this->worker = new ValidateVersionReleaseWorker($this->initTagParser(self::TAGS), '');
    }

    #[DataProvider('dataVersions')]
    public function testFindMostRecentVersion(string $version, string $exceptMaxVersion): void
    {
        $method = new \ReflectionMethod($this->worker, 'findMostRecentVersion');

        self::assertSame($method->invoke($this->worker, new Version(\strtolower($version))), $exceptMaxVersion);
    }

    public static function dataVersions(): \Traversable
    {
        yield ['0.1.1', '0.1.11'];
        yield ['0.1.11', '0.1.11'];
        yield ['0.1.99', '0.1.11'];
        yield ['0.2.0', '0.1.11'];
        yield ['1.0.0', '1.0.1'];
        yield ['1.0.1', '1.0.1'];
        yield ['1.0.2', '1.0.1'];
        yield ['1.1.0', '1.1.3'];
        yield ['1.1.3', '1.1.3'];
        yield ['1.1.4', '1.1.3'];
        yield ['1.2.0', '1.2.10'];
        yield ['1.2.10', '1.2.10'];
        yield ['1.2.11', '1.2.10'];
        yield ['2.0.0', '2.0.2'];
        yield ['2.0.2', '2.0.2'];
        yield ['2.1.0', '2.1.1'];
        yield ['2.1.1', '2.1.1'];
        yield ['2.1.2', '2.1.1'];
        yield ['3.0.0', '3.0.1'];
        yield ['3.0.1', '3.0.1'];
        yield ['3.0.2', '3.0.1'];
        yield ['3.1.0', '3.1.0'];
        yield ['3.1.1', '3.1.0'];
        yield ['4.0', '3.1.0'];
    }

    private function initTagParser(array $tags): TagParserInterface
    {
        return (new class($tags) implements TagParserInterface
        {
            public function __construct(
                private readonly array $tags
            ) {
            }

            public function parse(string $gitDirectory): array
            {
                return $this->tags;
            }
        });
    }
}
