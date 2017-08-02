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
use Spiral\Validation\Validator;
use TestApplication\Database\SampleRecord;

class IsLoadedConditionTest extends BaseTest
{
    public function testIsMet()
    {
        $validator = new Validator();

        /** @var \Spiral\Validation\CheckerConditionInterface $condition */
        $condition = $this->container->get(IsLoadedCondition::class)->withValidator($validator);

        $this->assertFalse($condition->isMet());

        $validator->setContext(['context']);
        $this->assertFalse($condition->isMet());

        $entity = new SampleRecord();
        $this->assertFalse($condition->isMet());

        $entity->save();
        $this->assertTrue($condition->isMet());
    }
}