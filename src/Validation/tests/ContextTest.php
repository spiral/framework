<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation;

class ContextTest extends BaseTest
{
    public function testNoRules(): void
    {
        $validator = $this->validation->validate([], [], ['context']);
        $this->assertSame(['context'], $validator->getContext());
    }
}
