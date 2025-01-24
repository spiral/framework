<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Http\Header\AcceptHeader;
use Spiral\Http\Header\AcceptHeaderItem;

class AcceptHeaderItemTest extends TestCase
{
    public static function emptyItemProvider(): iterable
    {
        $values = ['', ' '];
        foreach ($values as $value) {
            yield from [
                [AcceptHeaderItem::fromString($value)],
                [new AcceptHeaderItem($value)],
                [(new AcceptHeaderItem('*/*'))->withValue($value)],
            ];
        }
    }

    public static function valueProvider(): \Traversable
    {
        yield ['text/html'];
        yield ['text/*'];
        yield ['*/*'];
        yield ['*'];
    }

    public static function qualityBoundariesProvider(): iterable
    {
        $qualities = [-1, 0, 0.5, 1, 2];
        foreach ($qualities as $quality) {
            yield from [
                [$quality, AcceptHeaderItem::fromString("*;q=$quality")],
                [$quality, AcceptHeaderItem::fromString("*;Q=$quality")],
                [$quality, new AcceptHeaderItem('*', $quality)],
                [$quality, (new AcceptHeaderItem('*'))->withQuality($quality)],
            ];
        }
    }

    public static function paramsProvider(): iterable
    {
        $set = [
            [
                'expected' => [],
                'passed'   => [],
            ],
            [
                'expected' => ['a' => 'b'],
                'passed'   => ['a' => 'b'],
            ],
            [
                'expected' => [],
                'passed'   => ['c', '' => 'd', false, true, null, 1, '1' => 'e'],
            ],
        ];

        foreach ($set as $params) {
            $formattedParams = [];
            foreach ($params['passed'] as $k => $v) {
                $formattedParams[] = "$k=$v";
            }

            $formattedParams = implode(';', $formattedParams);

            yield from [
                [$params['expected'], AcceptHeaderItem::fromString("*;$formattedParams")],
                [$params['expected'], AcceptHeaderItem::fromString("*;$formattedParams")],
                [$params['expected'], new AcceptHeaderItem('*', 0, $params['passed'])],
                [$params['expected'], (new AcceptHeaderItem('*'))->withParams($params['passed'])],
            ];
        }

        $invalid = ['c', '' => 'd', false, true, null, [], new \stdClass(), 1, '1' => 'e'];
        yield from [
            [[], new AcceptHeaderItem('*', 0, $invalid)],
            [[], (new AcceptHeaderItem('*'))->withParams($invalid)],
        ];
    }

    public static function itemProvider(): iterable
    {
        $value = '*/*';

        yield from [
            [$value, new AcceptHeaderItem($value)],
            [$value, AcceptHeaderItem::fromString($value)],
            [$value, (new AcceptHeaderItem(''))->withValue($value)],
        ];

        $quality = 0.5;
        yield from [
            ["$value; q=$quality", new AcceptHeaderItem($value, $quality)],
            ["$value; q=$quality", AcceptHeaderItem::fromString("$value;Q=$quality")],
            ["$value; q=$quality", (new AcceptHeaderItem(''))->withValue($value)->withQuality($quality)],
        ];

        $params = ['a' => 'b', 'c' => 'd'];
        yield from [
            ["$value; q=$quality; a=b; c=d", new AcceptHeaderItem($value, $quality, $params)],
            ["$value; q=$quality; a=b; c=d", AcceptHeaderItem::fromString("$value;Q=$quality;a=b ; c = d")],
            [
                "$value; q=$quality; a=b; c=d",
                (new AcceptHeaderItem(''))->withValue($value)->withQuality($quality)->withParams($params),
            ],
        ];
    }

    #[DataProvider('emptyItemProvider')]
    public function testEmptyItem(AcceptHeaderItem $item): void
    {
        self::assertEmpty($item->getValue());
        self::assertSame('', (string) $item);
    }

    #[DataProvider('valueProvider')]
    public function testValue(string $value): void
    {
        $item = AcceptHeaderItem::fromString($value);
        self::assertSame($value, $item->getValue());

        $acceptHeader = new AcceptHeader([$item]);
        self::assertCount(1, $acceptHeader->getAll());

        $item = AcceptHeaderItem::fromString(" $value ");
        self::assertSame($value, $item->getValue());

        $acceptHeader = new AcceptHeader([$item]);
        self::assertCount(1, $acceptHeader->getAll());
        self::assertSame($value, (string) $acceptHeader->getAll()[0]);
    }

    #[DataProvider('qualityBoundariesProvider')]
    public function testItemQualityBoundaries(float $quality, AcceptHeaderItem $item): void
    {
        if ($quality > 1) {
            self::assertEqualsWithDelta(1.0, $item->getQuality(), PHP_FLOAT_EPSILON);
        }

        if ($quality < 0) {
            self::assertEqualsWithDelta(0.0, $item->getQuality(), PHP_FLOAT_EPSILON);
        }

        self::assertGreaterThanOrEqual(0, $item->getQuality());
        self::assertLessThanOrEqual(1, $item->getQuality());
    }

    #[DataProvider('paramsProvider')]
    public function testParams(array $params, AcceptHeaderItem $item): void
    {
        self::assertSame($params, $item->getParams());
    }

    #[DataProvider('itemProvider')]
    public function testItem(string $expected, AcceptHeaderItem $item): void
    {
        self::assertSame($expected, (string) $item);
    }
}
