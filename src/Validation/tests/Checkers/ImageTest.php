<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Tests\Validation\BaseTest;

class ImageTest extends BaseTest
{
    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = new Files();
        $this->container->bind(FilesInterface::class, $this->files);
    }

    public function testValid(): void
    {
        $file = __DIR__ . '/fixtures/sample-1.jpg';

        $this->assertValid([
            'i' => $file
        ], [
            'i' => ['image:valid']
        ]);

        $this->assertValid([
            'i' => $file
        ], [
            'i' => [['image:type', 'jpeg']]
        ]);

        $this->assertNotValid('i', [
            'i' => $file
        ], [
            'i' => [['image:type', 'png']]
        ]);

        $this->assertNotValid('i', [
            'i' => null
        ], [
            'i' => ['image:valid']
        ]);

        $this->assertNotValid('i', [
            'i' => []
        ], [
            'i' => ['image:valid']
        ]);

        $file = __DIR__ . '/fixtures/sample-2.png';

        $this->assertValid([
            'i' => $file
        ], [
            'i' => ['image:valid']
        ]);

        $file = __DIR__ . '/fixtures/sample-3.gif';

        $this->assertValid([
            'i' => $file
        ], [
            'i' => ['image:valid']
        ]);

        $file = __DIR__ . '/fixtures/hack.jpg';

        $this->assertNotValid('i', [
            'i' => $file
        ], [
            'i' => ['image:valid']
        ]);

        $this->assertNotValid('i', [
            'i' => [$file]
        ], [
            'i' => ['image:valid']
        ]);
    }

    public function testSmaller(): void
    {
        $file = __DIR__ . '/fixtures/sample-1.jpg';

        $this->assertValid([
            'i' => $file
        ], [
            'i' => [
                ['image:smaller', 350, 350]
            ]
        ]);

        $this->assertNotValid('i', [
            'i' => $file
        ], [
            'i' => [
                ['image:smaller', 150, 150]
            ]
        ]);


        $this->assertNotValid('i', [
            'i' => __DIR__ . '/fixtures/hack.jpg'
        ], [
            'i' => [
                ['image:smaller', 150, 150]
            ]
        ]);
    }

    public function testBigger(): void
    {
        $file = __DIR__ . '/fixtures/sample-1.jpg';

        $this->assertValid([
            'i' => $file
        ], [
            'i' => [
                ['image:bigger', 150, 140]
            ]
        ]);

        $this->assertNotValid('i', [
            'i' => $file
        ], [
            'i' => [
                ['image:bigger', 150, 150]
            ]
        ]);

        $this->assertNotValid('i', [
            'i' => __DIR__ . '/fixtures/hack.jpg'
        ], [
            'i' => [
                ['image:bigger', 150, 150]
            ]
        ]);
    }
}
