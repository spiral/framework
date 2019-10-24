<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Validation;

use Spiral\Framework\BaseTest;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidatorInterface;

class AliasTest extends BaseTest
{
    public function testEmpty(): void
    {
        $app = $this->makeApp();

        /** @var ValidatorInterface $v */
        $v = $app->get(ValidationInterface::class)->validate([
            'value' => ''
        ], [
            'value' => ['notEmpty']
        ]);

        $this->assertFalse($v->isValid());


        /** @var ValidatorInterface $v */
        $v = $app->get(ValidationInterface::class)->validate([
            'value' => 'abc'
        ], [
            'value' => ['notEmpty']
        ]);

        $this->assertTrue($v->isValid());
    }

    public function testAliased(): void
    {
        $app = $this->makeApp();

        /** @var ValidatorInterface $v */
        $v = $app->get(ValidationInterface::class)->validate([
            'value' => ''
        ], [
            'value' => ['aliased']
        ]);

        $this->assertFalse($v->isValid());


        /** @var ValidatorInterface $v */
        $v = $app->get(ValidationInterface::class)->validate([
            'value' => 'abc'
        ], [
            'value' => ['aliased']
        ]);

        $this->assertTrue($v->isValid());
    }
}
