<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Tests\BaseTest;
use Spiral\Validation\ValidatorInterface;

class ImageCheckerTest extends BaseTest
{
    public function testValid()
    {
        $file = __DIR__ . '/fixtures/sample-1.jpg';

        $this->assertValid([
            'i' => $file
        ], [
            'i' => ['image:valid']
        ]);

        $this->assertFail('i', [
            'i' => null
        ], [
            'i' => ['image:valid']
        ]);

        $this->assertFail('i', [
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

        $this->assertFail('i', [
            'i' => $file
        ], [
            'i' => ['image:valid']
        ]);
    }

    public function testSmaller()
    {
        $file = __DIR__ . '/fixtures/sample-1.jpg';

        $this->assertValid([
            'i' => $file
        ], [
            'i' => [
                ['image:smaller', 350, 350]
            ]
        ]);

        $this->assertFail('i', [
            'i' => $file
        ], [
            'i' => [
                ['image:smaller', 150, 150]
            ]
        ]);


        $this->assertFail('i', [
            'i' => __DIR__ . '/fixtures/hack.jpg'
        ], [
            'i' => [
                ['image:smaller', 150, 150]
            ]
        ]);
    }

    public function testBigger()
    {
        $file = __DIR__ . '/fixtures/sample-1.jpg';

        $this->assertValid([
            'i' => $file
        ], [
            'i' => [
                ['image:bigger', 150, 140]
            ]
        ]);

        $this->assertFail('i', [
            'i' => $file
        ], [
            'i' => [
                ['image:bigger', 150, 150]
            ]
        ]);

        $this->assertFail('i', [
            'i' => __DIR__ . '/fixtures/hack.jpg'
        ], [
            'i' => [
                ['image:bigger', 150, 150]
            ]
        ]);
    }

    protected function assertValid(array $data, array $rules)
    {
        $validator = $this->container->make(ValidatorInterface::class, ['rules' => $rules]);
        $validator->setData($data);

        $this->assertTrue($validator->isValid(), 'Validation FAILED');
    }

    protected function assertFail(string $error, array $data, array $rules)
    {
        $validator = $this->container->make(ValidatorInterface::class, ['rules' => $rules]);
        $validator->setData($data);

        $this->assertFalse($validator->isValid(), 'Validation PASSED');
        $this->assertArrayHasKey($error, $validator->getErrors());
    }
}