<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation;

use Spiral\Validation\Validator;

class ErrorsTest extends BaseTest
{
    public function testHasError(): void
    {
        /**
         * @var Validator $validator
         */
        $validator = $this->validation->validate([], ['name' => ['type::notEmpty']]);
        $this->assertTrue($validator->hasError('name'));
    }

    public function testMultipleErrors(): void
    {
        /**
         * @var Validator $validator
         */
        $validator = $this->validation->validate(
            [
                'name' => 'email'
            ],
            [
                'name' => [
                    'notEmpty',
                    'email',
                    ['string::regexp', '/^email@domain\.com$/']
                ]
            ]
        );

        $this->assertSame(
            'Must be a valid email address.',
            $validator->getErrors()['name']
        );
    }
}
