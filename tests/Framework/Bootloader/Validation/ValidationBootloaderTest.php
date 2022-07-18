<?php

declare(strict_types=1);

namespace Framework\Bootloader\Validation;

use Spiral\Tests\Framework\BaseTest;
use Spiral\Validation\ValidationProvider;
use Spiral\Validation\ValidationProviderInterface;

final class ValidationBootloaderTest extends BaseTest
{
    public function testValidationProviderInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ValidationProviderInterface::class, ValidationProvider::class);
    }
}
