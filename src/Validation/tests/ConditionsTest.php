<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Tests\Validation\Fixtures\PayloadCondition;
use Spiral\Tests\Validation\Fixtures\TestCondition;
use Spiral\Validation\Checker\AddressChecker;
use Spiral\Validation\Checker\FileChecker;
use Spiral\Validation\Checker\ImageChecker;
use Spiral\Validation\Checker\StringChecker;
use Spiral\Validation\Checker\TypeChecker;
use Spiral\Validation\Condition;
use Spiral\Validation\RulesInterface;

class ConditionsTest extends BaseTest
{
    public const CONFIG = [
        'checkers'   => [
            'file'    => FileChecker::class,
            'image'   => ImageChecker::class,
            'type'    => TypeChecker::class,
            'address' => AddressChecker::class,
            'string'  => StringChecker::class
        ],
        'conditions' => [
            'absent'     => Condition\AbsentCondition::class,
            'present'    => Condition\PresentCondition::class,
            'withAny'    => Condition\WithAnyCondition::class,
            'withoutAny' => Condition\WithoutAnyCondition::class,
            'withAll'    => Condition\WithAllCondition::class,
            'withAll2'   => Condition\WithAllCondition::class,
            'withoutAll' => Condition\WithoutAllCondition::class,
            'anyOf'      => Condition\AnyOfCondition::class,
            'noneOf'     => Condition\NoneOfCondition::class,
        ],
        'aliases'    => [
            'notEmpty' => 'type::notEmpty',
            'email'    => 'address::email',
            'url'      => 'address::url',
        ],
    ];


    /** @var RulesInterface */
    protected $rules;

    public function setUp(): void
    {
        parent::setUp();
        $this->rules = $this->container->get(RulesInterface::class);
    }

    public function testUnknown(): void
    {
        $this->expectException(NotFoundException::class);

        $rules = $this->rules->getRules([
            'i' => [
                'in_array',
                ['a', 'b'],
                'if' => 'unknownCondition'
            ],
        ]);

        iterator_to_array($rules);
    }

    public function testString(): void
    {
        $rules = $this->rules->getRules([
            'i' => [
                'in_array',
                ['a', 'b'],
                'if' => TestCondition::class
            ]
        ]);

        foreach ($rules as $rule) {
            $count = 0;
            foreach ($rule->getConditions() as $condition) {
                $this->assertInstanceOf(TestCondition::class, $condition);
                $count++;
            }

            $this->assertEquals(1, $count);
        }
    }

    public function testPayload(): void
    {
        $rules = $this->rules->getRules([
            'i' => [
                'in_array',
                ['a', 'b'],
                'if' => [PayloadCondition::class => 'j']
            ]
        ]);

        $validator = $this->validation->validate(['i' => 1, 'j' => 2], [], ['k' => 3]);
        foreach ($rules as $rule) {
            foreach ($rule->getConditions() as $condition) {
                $this->assertTrue($condition->isMet($validator, 'i', 1));
                $this->assertTrue($condition->isMet($validator, 'j', 2));
                $this->assertTrue($condition->isMet($validator, 'k', 3));

                $this->assertFalse($condition->isMet($validator, 'l', 4));
            }
        }
    }

    public function testAbsent(): void
    {
        $this->assertValid(
            ['i' => 'a',],
            ['i' => [['notEmpty', 'if' => ['absent' => ['b', 'c']]]]]
        );

        $this->assertValid(
            ['i' => 'a', 'b' => 'b', 'c' => 'c'],
            ['i' => [['is_bool', 'if' => ['absent' => ['b', 'c']]]]]
        );

        $this->assertNotValid(
            'i',
            ['i' => 'a'],
            ['i' => [['is_bool', 'if' => ['absent' => ['b', 'c']]]]]
        );
    }

    public function testPresent(): void
    {
        $this->assertValid(
            ['i' => '',],
            ['i' => [['notEmpty', 'if' => ['present' => ['b', 'c']]]]]
        );

        $this->assertValid(
            ['b' => 'b',],
            ['i' => [['notEmpty', 'if' => ['present' => ['i']]]]]
        );

        $this->assertNotValid(
            'i',
            ['i' => '',],
            ['i' => [['notEmpty', 'if' => ['present' => ['i']]]]]
        );

        $this->assertNotValid(
            'i',
            ['i' => 'a', 'b' => 'b', 'c' => 'c'],
            ['i' => [['is_bool', 'if' => ['present' => ['b', 'c']]]]]
        );
    }

    public function testWithAny(): void
    {
        $this->assertValid(
            ['i' => 'a',],
            ['i' => [['is_bool', 'if' => ['withAny' => ['b', 'c']]]]]
        );

        $this->assertNotValid(
            'i',
            ['i' => 'a', 'b' => 'b'],
            ['i' => [['is_bool', 'if' => ['withAny' => ['b', 'c']]]]]
        );

        $this->assertNotValid(
            'i',
            ['i' => 'a', 'b' => 'b', 'c' => 'c'],
            ['i' => [['is_bool', 'if' => ['withAny' => ['b', 'c']]]]]
        );
    }

    public function testWithAll(): void
    {
        $this->assertValid(
            ['i' => 'a',],
            ['i' => [['is_bool', 'if' => ['withAll' => ['b', 'c']]]]]
        );

        $this->assertValid(
            ['i' => 'a', 'b' => 'b'],
            ['i' => [['is_bool', 'if' => ['withAll' => ['b', 'c']]]]]
        );

        $this->assertNotValid(
            'i',
            ['i' => 'a', 'b' => 'b', 'c' => 'c'],
            ['i' => [['is_bool', 'if' => ['withAll' => ['b', 'c']]]]]
        );
    }

    public function testWithoutAny(): void
    {
        $this->assertNotValid(
            'i',
            ['i' => 'a',],
            ['i' => [['is_bool', 'if' => ['withoutAny' => ['b', 'c']]]]]
        );

        $this->assertNotValid(
            'i',
            ['i' => 'a', 'b' => 'b'],
            ['i' => [['is_bool', 'if' => ['withoutAny' => ['b', 'c']]]]]
        );

        $this->assertValid(
            ['i' => 'a', 'b' => 'b', 'c' => 'c'],
            ['i' => [['is_bool', 'if' => ['withoutAny' => ['b', 'c']]]]]
        );
    }

    public function testWithoutAll(): void
    {
        $this->assertNotValid(
            'i',
            ['i' => 'a',],
            ['i' => [['is_bool', 'if' => ['withoutAll' => ['b', 'c']]]]]
        );

        $this->assertValid(
            ['i' => 'a', 'b' => 'b'],
            ['i' => [['is_bool', 'if' => ['withoutAll' => ['b', 'c']]]]]
        );

        $this->assertValid(
            ['i' => 'a', 'b' => 'b', 'c' => 'c'],
            ['i' => [['is_bool', 'if' => ['withoutAll' => ['b', 'c']]]]]
        );
    }

    public function testAllOfConditions(): void
    {
        $this->assertValid(
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['withAll' => 'b', 'withoutAll' => 'c']]]]
        );
        $this->assertValid(
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['withAll' => 'b', 'withAll2' => 'd']]]]
        );
        $this->assertNotValid(
            'a',
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['withAll' => 'b', 'withAll2' => 'c']]]]
        );
        $this->assertNotValid(
            'a',
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => []]]]
        );
    }

    public function testAnyOfConditions(): void
    {
        $this->assertNotValid(
            'a',
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['anyOf' => ['withAll' => 'b', 'withoutAll' => 'c']]]]]
        );
        $this->assertNotValid(
            'a',
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['anyOf' => ['withAll' => 'b', 'withAll2' => 'd']]]]]
        );
        $this->assertNotValid(
            'a',
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['anyOf' => []]]]]
        );
        $this->assertValid(
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['anyOf' => ['withAll' => 'd', 'withoutAll' => 'c']]]]]
        );
    }

    public function testNoneOfConditions(): void
    {
        $this->assertNotValid(
            'a',
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['noneOf' => ['withoutAll' => ['b', 'c']]]]]]
        );
        $this->assertValid(
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['noneOf' => ['withAll' => 'b', 'withAll2' => 'd']]]]]
        );
        $this->assertValid(
            ['a' => 'a', 'b' => 'b', 'c' => 'c'],
            ['a' => [['is_bool', 'if' => ['noneOf' => ['withAll' => 'b', 'withAll2' => 'c']]]]]
        );
        $this->assertNotValid(
            'a',
            ['a' => 'a'],
            ['a' => [['is_bool', 'if' => ['noneOf' => []]]]]
        );
    }
}
