<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Pavel Z
 */

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Spiral\Http\Exception\AcceptHeaderException;
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

        $this->assertCount(0, $header->getAll());
        $this->assertSame('', (string)$header);
    }

    public function testHeaderSanitize(): void
    {
        $headers = AcceptHeader::fromString('text/*, text/html, ,;,text/html;level=1, */*')->getAll();

        $this->assertCount(3, $headers);
        $this->assertSame('text/html', $headers[0]->getValue());
        $this->assertSame('text/*', $headers[1]->getValue());
        $this->assertSame('*/*', $headers[2]->getValue());
    }

    public function testHeaderConstructingTypeError(): void
    {
        $this->expectException(AcceptHeaderException::class);

        new AcceptHeader(['*', 'UTF-8', ['text/html;level=1']]);
    }

    public function testImmutability(): void
    {
        $firstItem = AcceptHeaderItem::fromString('*/*;q=1');
        $secondItem = AcceptHeaderItem::fromString('text/*;q=0.9');
        $header = new AcceptHeader([$firstItem]);
        $firstItem->withValue('text/html');

        $this->assertSame('*/*', $header->add($secondItem)->getAll()[0]->getValue());
    }

    /**
     * @dataProvider sameQualityCompareProvider
     * @param string $input
     * @param string $a
     * @param string $b
     */
    public function testCompareWithEqualQuality(string $input, string $a, string $b): void
    {
        $headers = AcceptHeader::fromString($input)->getAll();

        $this->assertCount(2, $headers);
        $this->assertEquals($a, $headers[0]->getValue());
        $this->assertEquals($b, $headers[1]->getValue());
    }

    /**
     * @return iterable
     */
    public function sameQualityCompareProvider(): iterable
    {
        return [
            ['text/css;q=0.3, text/html;q=0.3', 'text/css', 'text/html'],
            ['text/html;q=0.3, text/css;q=0.3', 'text/html', 'text/css'],
            ['text/html;q=1, text/css', 'text/html', 'text/css'],
            ['text/html, text/css;q=1', 'text/html', 'text/css'],
        ];
    }

    public function testDuplicatedItems(): void
    {
        $header = AcceptHeader::fromString('*/*;q=0.9,text/html,*/*');
        $this->assertSame('text/html, */*', (string)$header);

        $header = AcceptHeader::fromString('text/html;q=0.4,*/*;q=0.9,text/html;q=0.6');
        $this->assertSame('*/*; q=0.9, text/html; q=0.6', (string)$header);
    }

    public function testAccessor(): void
    {
        $acceptHeader = AcceptHeader::fromString('text/css;q=0.3, text/html;q=0.3');
        $this->assertTrue($acceptHeader->has('tExt/css '));
        $this->assertFalse($acceptHeader->has('tExt/javascript'));

        $this->assertSame('text/css; q=0.3', (string)$acceptHeader->get('text/css'));
        $this->assertSame('text/html; q=0.3', (string)$acceptHeader->get('text/html'));
    }

    /**
     * @dataProvider addAndSortProvider
     * @param string $items
     * @param string $item
     * @param array  $expected
     */
    public function testAddAndSort(string $items, string $item, array $expected): void
    {
        $acceptHeader = AcceptHeader::fromString($items);
        $acceptHeader = $acceptHeader->add($item);

        $headers = $acceptHeader->getAll();
        $this->assertCount(count($expected), $headers);

        foreach ($expected as $i => $value) {
            $this->assertSame($value, $headers[$i]->getValue());
        }
    }

    /**
     * @return iterable
     */
    public function addAndSortProvider(): iterable
    {
        return [
            [
                'text/css;q=0.3,text/html;q=0.4',
                '',
                ['text/html', 'text/css']
            ],
            [
                'text/css;q=0.3,text/html;q=0.4',
                'text/javascript;q=0.35',
                ['text/html', 'text/javascript', 'text/css']
            ],
            [
                'text/css;q=0.3,text/html;q=0.4',
                'text/javascript;q=0.5',
                ['text/javascript', 'text/html', 'text/css']
            ],
            [
                'text/css;q=0.3,text/html;q=0.4',
                'text/javascript;q=.25',
                ['text/html', 'text/css', 'text/javascript']
            ],
        ];
    }

    /**
     * @dataProvider compareProvider
     * @param string $items
     * @param array  $expected
     */
    public function testCompare(string $items, array $expected): void
    {
        $acceptHeader = AcceptHeader::fromString($items);

        $headers = $acceptHeader->getAll();
        $this->assertCount(count($expected), $headers);

        foreach ($expected as $i => $value) {
            $this->assertSame($value, (string)$headers[$i]);
        }
    }

    /**
     * @return iterable
     */
    public function compareProvider(): iterable
    {
        return [
            //quality based
            ['text/html;q=0.8, text/css;q=0.9', ['text/css; q=0.9', 'text/html; q=0.8']],
            ['text/*;q=0.9, text/css;q=0.8;a=b;c=d', ['text/*; q=0.9', 'text/css; q=0.8; a=b; c=d']],
            ['text/html;q=1, text/*;', ['text/html', 'text/*']],
            ['text/html, text/css;q=1', ['text/html', 'text/css']],

            //.../subType based
            ['text/html, text/css', ['text/html', 'text/css']],
            ['text/css, text/html', ['text/css', 'text/html']],
            ['text/*, text/html', ['text/html', 'text/*']],
            ['text/html, text/*', ['text/html', 'text/*']],

            //type/... based
            ['text/html, */css', ['text/html', '*/css']],
            ['*/css,text/html', ['text/html', '*/css']],

            //value based
            ['text/*, text', ['text/*', 'text']],
            ['text, */*', ['text', '*/*']],
            ['text, *', ['text', '*']],
            ['*/*, text', ['text', '*/*']],
            ['*, text', ['text', '*']],
            ['*, */*', ['*', '*/*']],
            ['*/*,*', ['*/*', '*']],
            ['*,*', ['*']],

            //params count based
            ['text-html, text-css;a=b;c=d', ['text-css; a=b; c=d', 'text-html']],
            ['text-html;a=b;c=d;e=f, text-css;a=b;c=d', ['text-html; a=b; c=d; e=f', 'text-css; a=b; c=d']],
        ];
    }
}
