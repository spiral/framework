<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Validation;

use Spiral\Tests\Framework\BaseTest;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidatorInterface;

class CheckerTest extends BaseTest
{
    public function testEmpty(): void
    {
        $app = $this->makeApp();

        /** @var ValidatorInterface $v */
        $v = $app->get(ValidationInterface::class)->validate([
            'value' => 'cde'
        ], [
            'value' => ['my:abc']
        ]);

        $this->assertFalse($v->isValid());
        $this->assertSame('Not ABC', $v->getErrors()['value']);

        /** @var ValidatorInterface $v */
        $v = $app->get(ValidationInterface::class)->validate([
            'value' => 'abc'
        ], [
            'value' => ['my:abc']
        ]);

        $this->assertTrue($v->isValid());
    }
}
