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
use Spiral\Filters\Exception\ValidationException;
use Spiral\Tests\Framework\Filter\FilterTestCase;

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

        $this->assertInstanceOf(ProfileFilter::class, $filter);
        $this->assertInstanceOf(AddressFilter::class, $filter->address);

        $this->assertSame('John Doe', $filter->name);
        $this->assertSame('New York', $filter->address->city);
        $this->assertSame('Wall Street', $filter->address->address);
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

        $this->assertInstanceOf(UserFilter::class, $filter);
        $this->assertInstanceOf(PostFilter::class, $filter->postFilter);
        $this->assertInstanceOf(AuthorFilter::class, $filter->postFilter->author);

        $this->assertSame('John Doe', $filter->name);
        $this->assertSame('Some text', $filter->postFilter->body);
        $this->assertSame(1, $filter->postFilter->revision);
        $this->assertTrue($filter->postFilter->active);
        $this->assertSame(1.1, $filter->postFilter->postRating);
        $this->assertSame(2, $filter->postFilter->author->id);
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

        $this->assertInstanceOf(ProfileFilterWithPrefix::class, $filter);
        $this->assertInstanceOf(AddressFilter::class, $filter->address);

        $this->assertSame('John Doe', $filter->name);
        $this->assertSame('New York', $filter->address->city);
        $this->assertSame('Wall Street', $filter->address->address);
    }

    #[DataProvider('provideInvalidData')]
    public function testDataShouldBeValidated(array $data, array $expectedErrors, string $filter): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The given data was invalid.');

        try {
            $this->getFilter($filter, $data);
        } catch (ValidationException $e) {
            $this->assertEquals($expectedErrors, $e->errors);
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
