<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Filter\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\App\Request\AddressFilter;
use Spiral\App\Request\MultipleAddressesFilter;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\Filter\FilterTestCase;

#[TestScope(Spiral::HttpRequest)]
final class NestedArrayFiltersTest extends FilterTestCase
{
    public function testGetsNestedFilter(): void
    {
        $filter = $this->getFilter(MultipleAddressesFilter::class, post: [
            'name' => 'John Doe',
            'addresses' => [
                [
                    'city' => 'New York',
                    'address' => 'Wall Street',
                ],
                [
                    'city' => 'Los Angeles',
                    'address' => 'Hollywood',
                ],
            ],
        ]);

        self::assertInstanceOf(MultipleAddressesFilter::class, $filter);
        self::assertInstanceOf(AddressFilter::class, $filter->addresses[0]);
        self::assertInstanceOf(AddressFilter::class, $filter->addresses[1]);

        self::assertSame('John Doe', $filter->name);

        self::assertSame('New York', $filter->addresses[0]->city);
        self::assertSame('Wall Street', $filter->addresses[0]->address);

        self::assertSame('Los Angeles', $filter->addresses[1]->city);
        self::assertSame('Hollywood', $filter->addresses[1]->address);
    }

    #[DataProvider('provideInvalidData')]
    public function testDataShouldBeValidated(array $data, array $expectedErrors): void
    {
        if ($expectedErrors !== []) {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage('The given data was invalid.');
        }

        try {
            $filter = $this->getFilter(MultipleAddressesFilter::class, $data);
            self::assertSame('John Doe', $filter->name);
        } catch (ValidationException $e) {
            self::assertSame($expectedErrors, $e->errors);
            throw $e;
        }
    }

    public static function provideInvalidData(): \Generator
    {
        yield 'empty' => [
            [],
            [
                'name' => 'This value is required.',
            ],
        ];

        yield 'With name' => [
            ['name' => 'John Doe'],
            [],
        ];

        yield 'Without city' => [
            [
                'name' => 'John Doe',
                'addresses' => [
                    [
                        'address' => 'Wall Street',
                    ],
                    [
                        'address' => 'Hollywood',
                    ],
                ],
            ],
            [
                'addresses' => [
                    [
                        'city' => 'This value is required.'
                    ],
                    [
                        'city' => 'This value is required.'
                    ],
                ],
            ],
        ];

        yield 'Without city - 1' => [
            [
                'name' => 'John Doe',
                'addresses' => [
                    [
                        'city' => 'New York',
                        'address' => 'Wall Street',
                    ],
                    [
                        'address' => 'Hollywood',
                    ],
                ],
            ],
            [
                'addresses' => [
                    1 => [
                        'city' => 'This value is required.'
                    ],
                ],
            ],
        ];
    }
}

