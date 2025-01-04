<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Http\Header\AcceptHeader;
use Spiral\Http\Header\AcceptHeaderItem;

class AcceptHeaderTest extends TestCase
{
    public function testEmpty(): void
    {
        $header = new AcceptHeader([
            AcceptHeaderItem::fromString(''),
            new AcceptHeaderItem(''),
            (new AcceptHeaderItem('*/*'))->withValue('')
        ]);

        self::assertCount(0, $header->getAll());
        self::assertSame('', (string)$header);
    }

    public function testHeaderSanitize(): void
    {
        $headers = AcceptHeader::fromString('text/*, text/html, ,;,text/html;level=1, */*')->getAll();

        self::assertCount(3, $headers);
        self::assertSame('text/html', $headers[0]->getValue());
        self::assertSame('text/*', $headers[1]->getValue());
        self::assertSame('*/*', $headers[2]->getValue());
    }

    public function testImmutability(): void
    {
        $firstItem = AcceptHeaderItem::fromString('*/*;q=1');
        $secondItem = AcceptHeaderItem::fromString('text/*;q=0.9');
        $header = new AcceptHeader([$firstItem]);
        $firstItem->withValue('text/html');

        self::assertSame('*/*', $header->add($secondItem)->getAll()[0]->getValue());
    }

    #[DataProvider('sameQualityCompareProvider')]
    public function testCompareWithEqualQuality(string $input, string $a, string $b): void
    {
        $headers = AcceptHeader::fromString($input)->getAll();

        self::assertCount(2, $headers);
        self::assertEquals($a, $headers[0]->getValue());
        self::assertEquals($b, $headers[1]->getValue());
    }

    public static function sameQualityCompareProvider(): \Traversable
    {
        yield ['text/css;q=0.3, text/html;q=0.3', 'text/css', 'text/html'];
        yield ['text/html;q=0.3, text/css;q=0.3', 'text/html', 'text/css'];
        yield ['text/html;q=1, text/css', 'text/html', 'text/css'];
        yield ['text/html, text/css;q=1', 'text/html', 'text/css'];
    }

    public function testDuplicatedItems(): void
    {
        $header = AcceptHeader::fromString('*/*;q=0.9,text/html,*/*');
        self::assertSame('text/html, */*', (string)$header);

        $header = AcceptHeader::fromString('text/html;q=0.4,*/*;q=0.9,text/html;q=0.6');
        self::assertSame('*/*; q=0.9, text/html; q=0.6', (string)$header);
    }

    public function testAccessor(): void
    {
        $acceptHeader = AcceptHeader::fromString('text/css;q=0.3, text/html;q=0.3');
        self::assertTrue($acceptHeader->has('tExt/css '));
        self::assertFalse($acceptHeader->has('tExt/javascript'));

        self::assertSame('text/css; q=0.3', (string)$acceptHeader->get('text/css'));
        self::assertSame('text/html; q=0.3', (string)$acceptHeader->get('text/html'));
    }

    #[DataProvider('addAndSortProvider')]
    public function testAddAndSort(string $items, string $item, array $expected): void
    {
        $acceptHeader = AcceptHeader::fromString($items);
        $acceptHeader = $acceptHeader->add($item);

        $headers = $acceptHeader->getAll();
        self::assertCount(count($expected), $headers);

        foreach ($expected as $i => $value) {
            self::assertSame($value, $headers[$i]->getValue());
        }
    }

    public static function addAndSortProvider(): \Traversable
    {
        yield [
            'text/css;q=0.3,text/html;q=0.4',
            '',
            ['text/html', 'text/css']
        ];
        yield [
            'text/css;q=0.3,text/html;q=0.4',
            'text/javascript;q=0.35',
            ['text/html', 'text/javascript', 'text/css']
        ];
        yield [
            'text/css;q=0.3,text/html;q=0.4',
            'text/javascript;q=0.5',
            ['text/javascript', 'text/html', 'text/css']
        ];
        yield [
            'text/css;q=0.3,text/html;q=0.4',
            'text/javascript;q=.25',
            ['text/html', 'text/css', 'text/javascript']
        ];
    }

    #[DataProvider('compareProvider')]
    public function testCompare(string $items, array $expected): void
    {
        $acceptHeader = AcceptHeader::fromString($items);

        $headers = $acceptHeader->getAll();
        self::assertCount(count($expected), $headers);

        foreach ($expected as $i => $value) {
            self::assertSame($value, (string)$headers[$i]);
        }
    }

    public static function compareProvider(): \Traversable
    {
        //quality based
        yield ['text/html;q=0.8, text/css;q=0.9', ['text/css; q=0.9', 'text/html; q=0.8']];
        yield ['text/*;q=0.9, text/css;q=0.8;a=b;c=d', ['text/*; q=0.9', 'text/css; q=0.8; a=b; c=d']];
        yield ['text/html;q=1, text/*;', ['text/html', 'text/*']];
        yield ['text/html, text/css;q=1', ['text/html', 'text/css']];

        //.../subType based
        yield ['text/html, text/css', ['text/html', 'text/css']];
        yield ['text/css, text/html', ['text/css', 'text/html']];
        yield ['text/*, text/html', ['text/html', 'text/*']];
        yield ['text/html, text/*', ['text/html', 'text/*']];

        //type/... based
        yield ['text/html, */css', ['text/html', '*/css']];
        yield ['*/css,text/html', ['text/html', '*/css']];

        //value based
        yield ['text/*, text', ['text/*', 'text']];
        yield ['text, */*', ['text', '*/*']];
        yield ['text, *', ['text', '*']];
        yield ['*/*, text', ['text', '*/*']];
        yield ['*, text', ['text', '*']];
        yield ['*, */*', ['*', '*/*']];
        yield ['*/*,*', ['*/*', '*']];
        yield ['*,*', ['*']];

        //params count based
        yield ['text-html, text-css;a=b;c=d', ['text-css; a=b; c=d', 'text-html']];
        yield ['text-html;a=b;c=d;e=f, text-css;a=b;c=d', ['text-html; a=b; c=d; e=f', 'text-css; a=b; c=d']];
    }
}
