<?php

declare(strict_types=1);

namespace Framework\Console\Confirmation;

use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\Env;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\BaseTestCase;

#[TestScope(Spiral::Console)]
final class ApplicationInProductionCommandTest extends BaseTestCase
{
    #[Env('APP_ENV', 'production')]
    public function testRunCommandInProductionEnv(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('app-in-production', [], [
            'Application is in production.',
        ]);
    }

    #[Env('APP_ENV', 'testing')]
    public function testRunCommandInTestingEnv(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('app-in-production', [], [
            'Application is in testing.',
        ]);
    }
}
