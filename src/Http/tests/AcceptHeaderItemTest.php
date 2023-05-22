<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Http\Header\AcceptHeader;
use Spiral\Http\Header\AcceptHeaderItem;

class AcceptHeaderItemTest extends TestCase
{
    #[DataProvider('emptyItemProvider')]
    public function testEmptyItem(AcceptHeaderItem $item): void
    {
        $this->assertEmpty($item->getValue());
        $this->assertEquals('', (string)$item);
    }

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

    #[DataProvider('valueProvider')]
    public function testValue(string $value): void
    {
        $item = AcceptHeaderItem::fromString($value);
        $this->assertEquals($value, $item->getValue());

        $acceptHeader = new AcceptHeader([$item]);
        $this->assertCount(1, $acceptHeader->getAll());

        $item = AcceptHeaderItem::fromString(" $value ");
        $this->assertEquals($value, $item->getValue());

        $acceptHeader = new AcceptHeader([$item]);
        $this->assertCount(1, $acceptHeader->getAll());
        $this->assertEquals($value, (string)$acceptHeader->getAll()[0]);
    }

    public static function valueProvider(): \Traversable
    {
        yield ['text/html'];
        yield ['text/*'];
        yield ['*/*'];
        yield ['*'];
    }

    #[DataProvider('qualityBoundariesProvider')]
    public function testItemQualityBoundaries(float $quality, AcceptHeaderItem $item): void
    {
        if ($quality > 1) {
            $this->assertSame(1.0, $item->getQuality());
        }

        if ($quality < 0) {
            $this->assertSame(0.0, $item->getQuality());
        }

        $this->assertGreaterThanOrEqual(0, $item->getQuality());
        $this->assertLessThanOrEqual(1, $item->getQuality());
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

    #[DataProvider('paramsProvider')]
    public function testParams(array $params, AcceptHeaderItem $item): void
    {
        $this->assertSame($params, $item->getParams());
    }

    public static function paramsProvider(): iterable
    {
        $set = [
            [
                'expected' => [],
                'passed'   => []
            ],
            [
                'expected' => ['a' => 'b'],
                'passed'   => ['a' => 'b']
            ],
            [
                'expected' => [],
                'passed'   => ['c', '' => 'd', false, true, null, 1, '1' => 'e']
            ]
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
            [[], (new AcceptHeaderItem('*'))->withParams($invalid)]
        ];
    }

    #[DataProvider('itemProvider')]
    public function testItem(string $expected, AcceptHeaderItem $item): void
    {
        $this->assertSame($expected, (string)$item);
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
                (new AcceptHeaderItem(''))->withValue($value)->withQuality($quality)->withParams($params)
            ],
        ];
    }
}
