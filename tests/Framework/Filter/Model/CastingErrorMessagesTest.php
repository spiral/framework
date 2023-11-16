<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Filter\Model;

use Spiral\App\Request\CastingErrorMessages;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Tests\Framework\Filter\FilterTestCase;

final class CastingErrorMessagesTest extends FilterTestCase
{
    public function testValidationMessages(): void
    {
        try {
            $this->getFilter(CastingErrorMessages::class, [
                'uuid' => 'foo',
                'uuidWithValidationMessage' => 'foo',
                'uuidWithValidationMessageCallback' => 'foo',
            ]);
        } catch (ValidationException $e) {
            $this->assertSame([
                'uuid' => 'Unable to set UUID value. Invalid UUID string: foo',
                'uuidWithValidationMessage' => 'Invalid UUID',
                'uuidWithValidationMessageCallback' => 'Invalid UUID: foo. Error: Unable to set UUID value. Invalid UUID string: foo',
            ], $e->errors);
        }
    }
}
