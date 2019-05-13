<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Validation;

use Spiral\Framework\BaseTest;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidatorInterface;

class CheckerTest extends BaseTest
{
    public function testEmpty()
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