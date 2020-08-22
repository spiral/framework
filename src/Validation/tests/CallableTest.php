<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Spiral\Tests\Validation\Fixtures\TestChecker;
use Spiral\Tests\Validation\Fixtures\Value;
use Spiral\Validation\Checker\TypeChecker;

class CallableTest extends BaseTest
{
    public const CONFIG = [
        'checkers' => [
            'type' => TypeChecker::class,
            'test' => TestChecker::class,
        ],
        'aliases'  => [
            'notEmpty' => 'type::notEmpty',
        ],
    ];

    public function testInArray(): void
    {
        $this->assertValid([
            'i' => 'value'
        ], [
            'i' => [
                ['in_array', ['value', 'other']]
            ]
        ]);

        $this->assertNotValid('i', [
            'i' => 'third'
        ], [
            'i' => [
                ['in_array', ['value', 'other']]
            ]
        ]);
    }

    public function testInArrayAccessor(): void
    {
        $this->assertValid([
            'i' => new Value('value')
        ], [
            'i' => [
                ['in_array', ['value', 'other']]
            ]
        ]);

        $this->assertNotValid('i', [
            'i' => new Value('third')
        ], [
            'i' => [
                ['in_array', ['value', 'other']]
            ]
        ]);
    }

    public function testEmptyInArray(): void
    {
        $this->assertValid([
        ], [
            'i' => [
                ['in_array', ['value', 'other']]
            ]
        ]);

        $this->assertNotValid('i', [
        ], [
            'i' => [
                ['notEmpty'],
                ['in_array', ['value', 'other']]
            ]
        ]);

        $this->assertNotValid('i', [
            'i' => null
        ], [
            'i' => [
                ['notEmpty'],
                ['in_array', ['value', 'other']]
            ]
        ]);
    }

    public function testDefaultMessage(): void
    {
        $v = $this->validation->validate([
            'i' => 'third'
        ], [
            'i' => [
                ['in_array', ['value', 'other']]
            ]
        ]);

        $this->assertSame('The condition `in_array` was not met.', $v->getErrors()['i']);
    }

    public function testDefaultMessageStatic(): void
    {
        $v = $this->validation->validate([
            'i' => 'third'
        ], [
            'i' => [
                [[self::class, 'check']]
            ]
        ]);

        $this->assertSame(
            'The condition `Spiral\Tests\Validation\CallableTest::check` was not met.',
            $v->getErrors()['i']
        );
    }

    public function testDefaultMessageRuntime(): void
    {
        $v = $this->validation->validate([
            'i' => 'third'
        ], [
            'i' => [
                [[$this, 'check']]
            ]
        ]);

        $this->assertSame(
            'The condition `Spiral\Tests\Validation\CallableTest::check` was not met.',
            $v->getErrors()['i']
        );
    }

    public function testDefaultMethodClosure(): void
    {
        $v = $this->validation->validate([
            'i' => 'third'
        ], [
            'i' => static function () {
                return false;
            }
        ]);

        $this->assertSame('The condition `~user-defined~` was not met.', $v->getErrors()['i']);
    }

    public function testCustomMessage(): void
    {
        $v = $this->validation->validate([
            'i' => 'third'
        ], [
            'i' => [
                ['notEmpty'],
                ['in_array', ['value', 'other'], 'msg' => 'error']
            ]
        ]);

        $this->assertSame('error', $v->getErrors()['i']);
    }

    public function testCheckerDefault(): void
    {
        $validator = $this->validation->validate(
            ['i' => 'value'],
            ['i' => 'test:test']
        );

        $this->assertSame(['i' => 'The condition `test` was not met.'], $validator->getErrors());
    }

    public function testCheckerByCallableClass(): void
    {
        $validator = $this->validation->validate(
            [
                'i' => 'value'
            ],
            [
                'i' => [
                    [
                        [TestChecker::class, 'test'],
                        'err' => 'ERROR'
                    ]
                ]
            ]
        );

        $this->assertSame(['i' => 'ERROR'], $validator->getErrors());
    }

    public function testCheckerByCallableObject(): void
    {
        $checker = new TestChecker();
        $validator = $this->validation->validate(
            ['i' => 'value'],
            [
                'i' => [
                    [[$checker, 'test'], 'err' => 'ERROR']
                ]
            ]
        );

        $this->assertSame(['i' => 'ERROR'], $validator->getErrors());
    }

    public static function check($value): bool
    {
        return false;
    }
}
