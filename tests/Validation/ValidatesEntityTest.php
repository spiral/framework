<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Validation;

use Spiral\Tests\BaseTest;
use Spiral\Tests\Validation\Fixtures\NestedEntity;
use Spiral\Tests\Validation\Fixtures\SampleEntity;
use Spiral\Validation\ValidatorInterface;

class ValidatesEntityTest extends BaseTest
{
    public function testEntityFails()
    {
        /** @var SampleEntity $e */
        $e = $this->container->make(SampleEntity::class, [
            'data' => []
        ]);

        $this->assertFalse($e->isValid());
        $this->assertFalse($e->isValid('value'));
        $this->assertFalse($e->isValid('string'));

        $this->assertArrayHasKey('value', $e->getErrors());
        $this->assertArrayHasKey('string', $e->getErrors());
    }

    public function testCheckValidator()
    {
        /** @var SampleEntity $e */
        $e = $this->container->make(SampleEntity::class, [
            'data' => []
        ]);

        $this->assertInstanceOf(ValidatorInterface::class, $e->getValidator());

        //Exchange
        $e->setValidator($e->getValidator());
    }

    public function testValidatePassed()
    {
        /** @var SampleEntity $e */
        $e = $this->container->make(SampleEntity::class, [
            'data' => [
                'value'  => 123,
                'string' => '123456789'
            ]
        ]);

        $this->assertTrue($e->isValid());
    }

    public function testFailPartially()
    {
        /** @var SampleEntity $e */
        $e = $this->container->make(SampleEntity::class, [
            'data' => [
                'value'  => 123,
                'string' => '12345678901'
            ]
        ]);

        $this->assertFalse($e->isValid());

        $this->assertTrue($e->isValid('value'));
        $this->assertFalse($e->isValid('string'));

        $this->assertArrayNotHasKey('value', $e->getErrors());
        $this->assertArrayHasKey('string', $e->getErrors());
    }

    public function testFailNested()
    {
        /** @var SampleEntity $e */
        $e = $this->container->make(SampleEntity::class, [
            'data' => [
                'value'  => 123,
                'string' => '123456789'
            ]
        ]);

        $e->nested = $this->container->make(NestedEntity::class, [
            'data' => []
        ]);

        $this->assertFalse($e->isValid());
        $this->assertFalse($e->nested->isValid());

        $this->assertTrue($e->isValid('value'));
        $this->assertTrue($e->isValid('string'));
        $this->assertFalse($e->isValid('nested'));

        $this->assertArrayNotHasKey('value', $e->getErrors());
        $this->assertArrayNotHasKey('string', $e->getErrors());
        $this->assertArrayHasKey('nested', $e->getErrors());
        $this->assertInternalType('array', $e->getErrors()['nested']);

        $this->assertArrayHasKey('thing', $e->getErrors()['nested']);
    }

    public function testPassNested()
    {
        /** @var SampleEntity $e */
        $e = $this->container->make(SampleEntity::class, [
            'data' => [
                'value'  => 123,
                'string' => '123456789'
            ]
        ]);

        $e->nested = $this->container->make(NestedEntity::class, [
            'data' => [
                'thing' => 123
            ]
        ]);

        $this->assertTrue($e->isValid());
        $this->assertTrue($e->nested->isValid());

        $this->assertTrue($e->isValid('value'));
        $this->assertTrue($e->isValid('string'));
        $this->assertTrue($e->isValid('nested'));
    }

    public function testFailNestedArray()
    {
        /** @var SampleEntity $e */
        $e = $this->container->make(SampleEntity::class, [
            'data' => [
                'value'  => 123,
                'string' => '123456789'
            ]
        ]);

        $e->nested = [
            'one' => $this->container->make(NestedEntity::class, ['data' => []]),
            'two' => $this->container->make(NestedEntity::class, ['data' => ['thing' => 900]]),
        ];

        $this->assertFalse($e->isValid());

        $this->assertTrue($e->isValid('value'));
        $this->assertTrue($e->isValid('string'));
        $this->assertFalse($e->isValid('nested'));

        $this->assertArrayNotHasKey('value', $e->getErrors());
        $this->assertArrayNotHasKey('string', $e->getErrors());
        $this->assertArrayHasKey('nested', $e->getErrors());
        $this->assertInternalType('array', $e->getErrors()['nested']);

        $this->assertArrayHasKey('one', $e->getErrors()['nested']);
        $this->assertArrayNotHasKey('two', $e->getErrors()['nested']);

        $this->assertInternalType('array', $e->getErrors()['nested']['one']);
        $this->assertArrayHasKey('thing', $e->getErrors()['nested']['one']);
    }
}