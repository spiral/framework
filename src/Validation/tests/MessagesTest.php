<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation;

class MessagesTest extends BaseTest
{
    public function testDefault(): void
    {
        $validator = $this->validation->validate([], ['name' => ['type::notEmpty']]);
        $this->assertSame(['name' => 'This value is required.'], $validator->getErrors());
    }

    public function testMessage(): void
    {
        $validator = $this->validation->validate([], [
            'name' => [
                ['type::notEmpty', 'message' => 'Value is empty.']
            ]
        ]);
        $this->assertSame(['name' => 'Value is empty.'], $validator->getErrors());
    }

    public function testMsg(): void
    {
        $validator = $this->validation->validate([], [
            'name' => [
                ['type::notEmpty', 'msg' => 'Value is empty.']
            ]
        ]);
        $this->assertSame(['name' => 'Value is empty.'], $validator->getErrors());
    }

    public function testError(): void
    {
        $validator = $this->validation->validate([], [
            'name' => [
                ['type::notEmpty', 'error' => 'Value is empty.']
            ]
        ]);
        $this->assertSame(['name' => 'Value is empty.'], $validator->getErrors());
    }

    public function testErr(): void
    {
        $validator = $this->validation->validate([], [
            'name' => [
                ['type::notEmpty', 'err' => 'Value is empty.']
            ]
        ]);
        $this->assertSame(['name' => 'Value is empty.'], $validator->getErrors());
    }
}
