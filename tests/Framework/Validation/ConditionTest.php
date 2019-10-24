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

class ConditionTest extends BaseTest
{
    public function testEmpty(): void
    {
        $app = $this->makeApp();

        /** @var ValidatorInterface $v */
        $v = $app->get(ValidationInterface::class)->validate([
            'value' => ''
        ], [
            'value' => [
                [
                    'my:abc',
                    'if' => 'cond'
                ]
            ]
        ]);

        $this->assertTrue($v->isValid());

        /** @var ValidatorInterface $v */
        $v = $app->get(ValidationInterface::class)->validate([
            'value' => 'value'
        ], [
            'value' => [
                [
                    'my:abc',
                    'if' => 'cond'
                ]
            ]
        ]);

        $this->assertFalse($v->isValid());
        $this->assertSame('Not ABC', $v->getErrors()['value']);
    }
}
