<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Filter\Model;

use Spiral\App\Request\AddressFilter;
use Spiral\App\Request\ProfileFilter;
use Spiral\App\Request\ProfileFilterWithPrefix;
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

    /**
     * @dataProvider provideInvalidData
     */
    public function testDataShouldBeValidated(array $data, array $expectedErrors): void
    {
        $this->expectException(ValidationException::class);
        $this->expectErrorMessage('The given data was invalid.');

        try {
            $this->getFilter(ProfileFilter::class, $data);
        } catch (ValidationException $e) {
            $this->assertSame($expectedErrors, $e->errors);
            throw $e;
        }
    }

    public function provideInvalidData(): \Generator
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
        ];
    }
}
