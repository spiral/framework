<?php

declare(strict_types=1);

namespace Framework\Filter\Model;

use Spiral\App\Request\FilterWithSetters;
use Spiral\App\Request\PostFilter;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Tests\Framework\Filter\FilterTestCase;

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
            'nullableString' => null
        ]);

        $this->assertInstanceOf(FilterWithSetters::class, $filter);

        $this->assertSame(1, $filter->integer);
        $this->assertSame('&lt;b&gt;&quot;test&quot;&lt;/b&gt;', $filter->string);
        $this->assertNull($filter->nullableString);
    }

    public function testSettersWithValidation(): void
    {
        $filter = $this->getFilter(PostFilter::class, [
            'body' => 'foo',
            'revision' => '1',
            'active' => '1',
            'post_rating' => '0.9',
            'author' => [
                'id' => '3'
            ]
        ]);

        $this->assertInstanceOf(PostFilter::class, $filter);

        $this->assertSame('foo', $filter->body);
        $this->assertSame(1, $filter->revision);
        $this->assertTrue($filter->active);
        $this->assertSame(0.9, $filter->postRating);
        $this->assertSame(3, $filter->author->id);
    }

    public function testExtendedSetter(): void
    {
        $filter = $this->getFilter(FilterWithSetters::class, [
            'amount' => 10,
        ]);

        $this->assertSame(15, $filter->amount);
    }

    public function testSetterException(): void
    {
        try {
            $this->getFilter(FilterWithSetters::class, [
                'uuid' => 'foo',
            ]);
        } catch (ValidationException $e) {
            $this->assertEquals(['uuid' => 'Unable to set value. The given data was invalid.'], $e->errors);
        }
    }
}
