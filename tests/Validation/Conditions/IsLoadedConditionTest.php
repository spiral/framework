<?php
/**
 * Created by PhpStorm.
 * User: Valentin
 * Date: 02.08.2017
 * Time: 10:58
 */

namespace Spiral\Tests\Validation\Conditions;


use Spiral\Tests\BaseTest;
use Spiral\Tests\Validation\Fixtures\IsLoadedCondition;
use Spiral\Validation\Configs\ValidatorConfig;
use Spiral\Validation\Validator;
use TestApplication\Database\SampleRecord;

class IsLoadedConditionTest extends BaseTest
{
    protected function getConfig()
    {
        return new ValidatorConfig([
            'emptyConditions' => [
                'notEmpty',
                'type::notEmpty',
            ],
            'checkers'        => [],
            'aliases'         => [
                'notEmpty' => 'type::notEmpty',
                'email'    => 'address::email',
                'url'      => 'address::url',
            ],
        ]);
    }

    public function testIsMet()
    {
        $this->commands->run('orm:schema', [
            '--alter' => true
        ]);

        $validator = new Validator();

        /** @var \Spiral\Validation\CheckerConditionInterface $condition */
        $condition = $this->container->get(IsLoadedCondition::class)->withValidator($validator);

        $this->assertFalse($condition->isMet());

        $validator->setContext(['context']);
        $this->assertFalse($condition->isMet());

        $entity = new SampleRecord();
        $validator->setContext($entity);
        $this->assertFalse($condition->isMet());

        $entity->save();
        $this->assertTrue($condition->isMet());
    }

    public function testWithConditions()
    {
        //Validator works
        $validator = new Validator(
            ['email' => ['notEmpty', 'address::email']],
            ['email' => 'some@email.com'],
            $this->getConfig(),
            $this->container
        );
        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            ['email' => ['notEmpty', 'address::email']],
            ['email' => null],
            $this->getConfig(),
            $this->container
        );
        $this->assertFalse($validator->isValid());

        //Condition will not met because no context
        $validator = new Validator(
            [
                'email' => [
                    ['notEmpty', 'condition' => IsLoadedCondition::class],
                    ['address::email', 'condition' => IsLoadedCondition::class],
                ]
            ],
            ['email' => null],
            $this->getConfig(),
            $this->container
        );
        $this->assertTrue($validator->isValid());

        //Condition will not met because context should be entity
        $validator = new Validator(
            [
                'email' => [
                    ['notEmpty', 'condition' => IsLoadedCondition::class],
                    ['address::email', 'condition' => IsLoadedCondition::class],
                ]
            ],
            ['email' => null],
            $this->getConfig(),
            $this->container
        );
        $validator->setContext(['some', 'context']);
        $this->assertTrue($validator->isValid());

        //Condition will not met because context should be loaded entity
        $entity = new SampleRecord();
        $validator->setContext($entity);
        $this->assertTrue($validator->isValid());

        //Condition will met and validator will fail check
        $entity = new SampleRecord();
        $entity->save();
        $validator->setContext($entity);
        $this->assertFalse($validator->isValid());

        //Validator will fail because condition should exist and be instance of \Spiral\Validation\CheckerConditionInterface::class
        $validator = new Validator(
            [
                'email' => [
                    ['notEmpty', 'condition' => 'Some\Condition'],
                    ['address::email', 'condition' => 'Some\Condition'],
                ]
            ],
            ['email' => null],
            $this->getConfig(),
            $this->container
        );

        $entity = new SampleRecord();
        $entity->save();

        $validator->setContext($entity);
        $this->assertFalse($validator->isValid());
    }
}