<?php

declare(strict_types=1);

namespace Framework\Filter\Model;

use Spiral\App\Request\FilterWithSetters;
use Spiral\App\Request\PostFilter;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\Filter\FilterTestCase;

#[TestScope(Spiral::HttpRequest)]
final class FilterWithSettersTest extends FilterTestCase
{
    public function testSetters(): void
    {
        $filter = $this->getFilter(FilterWithSetters::class, [
            'integer' => '1',
            'string' => new class implements \Stringable {
                public function __toString()
                {
                    return '--<b>"test"</b>  ';
                }
            },
            'nullableString' => null,
        ]);

        self::assertInstanceOf(FilterWithSetters::class, $filter);

        self::assertSame(1, $filter->integer);
        self::assertSame('&lt;b&gt;&quot;test&quot;&lt;/b&gt;', $filter->string);
        self::assertNull($filter->nullableString);
    }

    public function testSettersWithValidation(): void
    {
        $filter = $this->getFilter(PostFilter::class, [
            'body' => 'foo',
            'revision' => '1',
            'active' => '1',
            'post_rating' => '0.9',
            'author' => [
                'id' => '3',
            ],
        ]);

        self::assertInstanceOf(PostFilter::class, $filter);

        self::assertSame('foo', $filter->body);
        self::assertSame(1, $filter->revision);
        self::assertTrue($filter->active);
        self::assertEqualsWithDelta(0.9, $filter->postRating, PHP_FLOAT_EPSILON);
        self::assertSame(3, $filter->author->id);
    }

    public function testExtendedSetter(): void
    {
        $filter = $this->getFilter(FilterWithSetters::class, [
            'amount' => 10,
        ]);

        self::assertSame(15, $filter->amount);
    }

    public function testSetterException(): void
    {
        try {
            $this->getFilter(FilterWithSetters::class, [
                'uuid' => 'foo',
            ]);
        } catch (ValidationException $e) {
            self::assertSame(['uuid' => 'Unable to set value. The given data was invalid.'], $e->errors);
        }
    }
}
