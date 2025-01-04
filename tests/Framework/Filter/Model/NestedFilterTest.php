<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Filter\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\App\Request\AddressFilter;
use Spiral\App\Request\AuthorFilter;
use Spiral\App\Request\PostFilter;
use Spiral\App\Request\ProfileFilter;
use Spiral\App\Request\ProfileFilterWithPrefix;
use Spiral\App\Request\UserFilter;
use Spiral\App\Request\WithNullableNestedFilter;
use Spiral\App\Request\WithNullableRequiredNestedFilter;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\Filter\FilterTestCase;

#[TestScope(Spiral::HttpRequest)]
final class NestedFilterTest extends FilterTestCase
{
    public function testGetsNestedFilter(): void
    {
        $filter = $this->getFilter(ProfileFilter::class, [
            'name' => 'John Doe',
            'address' => [
                'city' => 'New York',
                'address' => 'Wall Street',
            ],
        ]);

        self::assertInstanceOf(ProfileFilter::class, $filter);
        self::assertInstanceOf(AddressFilter::class, $filter->address);

        self::assertSame('John Doe', $filter->name);
        self::assertSame('New York', $filter->address->city);
        self::assertSame('Wall Street', $filter->address->address);
    }

    public function testGetsNestedFilterWithOtherNestedFilter(): void
    {
        $filter = $this->getFilter(UserFilter::class, [
            'name' => 'John Doe',
            'postFilter' => [
                'body' => 'Some text',
                'revision' => 1,
                'active' => true,
                'post_rating' => 1.1,
                'author' => [
                    'id' => 2
                ],
            ]
        ]);

        self::assertInstanceOf(UserFilter::class, $filter);
        self::assertInstanceOf(PostFilter::class, $filter->postFilter);
        self::assertInstanceOf(AuthorFilter::class, $filter->postFilter->author);

        self::assertSame('John Doe', $filter->name);
        self::assertSame('Some text', $filter->postFilter->body);
        self::assertSame(1, $filter->postFilter->revision);
        self::assertTrue($filter->postFilter->active);
        self::assertEqualsWithDelta(1.1, $filter->postFilter->postRating, PHP_FLOAT_EPSILON);
        self::assertSame(2, $filter->postFilter->author->id);
    }

    public function testGetsNestedFilterWithCustomPrefix(): void
    {
        $filter = $this->getFilter(ProfileFilterWithPrefix::class, [
            'name' => 'John Doe',
            'addr' => [
                'city' => 'New York',
                'address' => 'Wall Street',
            ],
        ]);

        self::assertInstanceOf(ProfileFilterWithPrefix::class, $filter);
        self::assertInstanceOf(AddressFilter::class, $filter->address);

        self::assertSame('John Doe', $filter->name);
        self::assertSame('New York', $filter->address->city);
        self::assertSame('Wall Street', $filter->address->address);
    }

    public function testGetsNullableNestedFilterWithoutData(): void
    {
        $filter = $this->getFilter(WithNullableNestedFilter::class, [
            'name' => 'John Doe'
        ]);

        self::assertSame('John Doe', $filter->name);
        self::assertNull($filter->address);
    }

    public function testGetsNullableRequiredNestedFilterWithoutData(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The given data was invalid.');

        $this->getFilter(WithNullableRequiredNestedFilter::class, [
            'name' => 'John Doe'
        ]);
    }

    #[DataProvider('provideInvalidData')]
    public function testDataShouldBeValidated(array $data, array $expectedErrors, string $filter): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The given data was invalid.');

        try {
            $this->getFilter($filter, $data);
        } catch (ValidationException $e) {
            self::assertEquals($expectedErrors, $e->errors);
            throw $e;
        }
    }

    public static function provideInvalidData(): \Generator
    {
        yield 'empty' => [
            [],
            [
                'address' => [
                    'city' => 'This value is required.',
                    'address' => 'This value is required.',
                ],
                'name' => 'This value is required.',
            ],
            ProfileFilter::class
        ];

        yield 'only-address' => [
            [
                'address' => [
                    'city' => 'New York',
                    'address' => 'Wall Street',
                ],
            ],
            [
                'name' => 'This value is required.',
            ],
            ProfileFilter::class
        ];

        yield 'only-name' => [
            [
                'name' => 'John Doe',
            ],
            [
                'address' => [
                    'city' => 'This value is required.',
                    'address' => 'This value is required.',
                ],
            ],
            ProfileFilter::class
        ];

        yield 'name and city' => [
            [
                'name' => 'John Doe',
                'address' => [
                    'city' => 'New York',
                ],
            ],
            [
                'address' => [
                    'address' => 'This value is required.',
                ],
            ],
            ProfileFilter::class
        ];
        yield 'nested filter with other nested filter' => [
            [],
            [
                'name' => 'This value is required.',
                'postFilter' => [
                    'body' => 'This value is required.',
                    'revision' => 'This value is required.',
                    'active' => 'This value is required.',
                    'post_rating' => 'This value is required.',
                    'author' => [
                        'id' => 'This value is required.',
                    ],
                ],
            ],
            UserFilter::class
        ];
    }
}
