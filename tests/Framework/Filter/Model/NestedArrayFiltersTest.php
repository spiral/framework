<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Filter\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\App\Request\AddressFilter;
use Spiral\App\Request\MultipleAddressesFilter;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Tests\Framework\Filter\FilterTestCase;

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

        $this->assertInstanceOf(MultipleAddressesFilter::class, $filter);
        $this->assertInstanceOf(AddressFilter::class, $filter->addresses[0]);
        $this->assertInstanceOf(AddressFilter::class, $filter->addresses[1]);

        $this->assertSame('John Doe', $filter->name);

        $this->assertSame('New York', $filter->addresses[0]->city);
        $this->assertSame('Wall Street', $filter->addresses[0]->address);

        $this->assertSame('Los Angeles', $filter->addresses[1]->city);
        $this->assertSame('Hollywood', $filter->addresses[1]->address);
    }

    #[DataProvider('provideInvalidData')]
    public function testDataShouldBeValidated(array $data, array $expectedErrors): void
    {
        if ($expectedErrors !== []) {
            $this->expectException(ValidationException::class);
            $this->expectErrorMessage('The given data was invalid.');
        }

        try {
            $filter = $this->getFilter(MultipleAddressesFilter::class, $data);
            $this->assertSame('John Doe', $filter->name);
        } catch (ValidationException $e) {
            $this->assertSame($expectedErrors, $e->errors);
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

