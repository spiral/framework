<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Input\ArrayInput;
use Spiral\DataGrid\InputMapper;

class MapperTest extends TestCase
{
    public function testMapper(): void
    {
        $mapper = new InputMapper(
            [
                'sortByNewest'         => 'sort.newest',
                'sortMissed'           => 'sort.missed',
                'sort.as.super.nested' => 'sort.as.nested',
                'sort.byOldestSort'    => 'sort.by.oldest',
            ]
        );
        $mapper = $mapper->withInput(
            new ArrayInput(
                [
                    'sortByNewest' => true,
                    'sort'         => [
                        'as'           => [
                            'super' => [
                                'nested' => 'desc'
                            ]
                        ],
                        'byOldestSort' => false,
                    ]
                ]
            )
        );

        $this->assertTrue($mapper->hasOption('sort'));
        $this->assertFalse($mapper->hasOption('filter'));
        $this->assertSame(
            [
                'newest' => true,
                'as'     => ['nested' => 'desc'],
                'by'     => ['oldest' => false],
            ],
            $mapper->getOption('sort')
        );
    }

    public function testWithoutMapping(): void
    {
        $mapper = new InputMapper(
            [
                'sortByNewest'         => 'sort.newest',
                'sort.as.super.nested' => 'sort.as.nested',
            ]
        );
        $mapper = $mapper->withInput(
            new ArrayInput(
                [
                    'sortByNewest' => true,
                    'sort'         => [
                        'as'           => [
                            'super' => [
                                'nested' => 'desc'
                            ]
                        ],
                        'byOldestSort' => false,
                    ],
                    'filter'       => [
                        'byOldestSort' => false
                    ]
                ]
            )
        );

        $this->assertTrue($mapper->hasOption('sort'));
        $this->assertTrue($mapper->hasOption('filter'));
        $this->assertSame(
            [
                'newest'       => true,
                'as'           => ['nested' => 'desc'],
                'byOldestSort' => false,
            ],
            $mapper->getOption('sort')
        );
        $this->assertSame(
            [
                'byOldestSort' => false,
            ],
            $mapper->getOption('filter')
        );
    }

    public function testMapperWithExtraValues(): void
    {
        $mapper = new InputMapper(
            [
                'sortByNewest'         => 'sort.newest',
                'sort.as.super.nested' => 'sort.as.nested',
                'sort.byOldestSort'    => 'filter.by.oldest',
            ]
        );
        $mapper = $mapper->withInput(
            new ArrayInput(
                [
                    'sortByNewest' => true,
                    'sort'         => [
                        'as'           => [
                            'super' => [
                                'nested' => 'desc'
                            ]
                        ],
                        'byOldestSort' => false,
                    ],
                    'filter'       => [
                        'byOldestSort' => false
                    ]
                ]
            )
        );

        $this->assertTrue($mapper->hasOption('sort'));
        $this->assertTrue($mapper->hasOption('filter'));
        $this->assertSame(
            [
                'newest' => true,
                'as'     => ['nested' => 'desc']
            ],
            $mapper->getOption('sort')
        );
        $this->assertSame(
            [
                'by' => ['oldest' => false]
            ],
            $mapper->getOption('filter')
        );
    }
}
