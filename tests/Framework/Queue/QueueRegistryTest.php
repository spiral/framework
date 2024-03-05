<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Queue;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Spiral\App\Job\SampleJob;
use Spiral\App\Job\Task;
use Spiral\App\Job\TaskInterface;
use Spiral\Framework\Spiral;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\BaseTestCase;

final class QueueRegistryTest extends BaseTestCase
{
    #[TestScope(Spiral::Queue, [TaskInterface::class => Task::class])]
    #[DoesNotPerformAssertions]
    public function testHandleJobWithDependencyInScope(): void
    {
        /** @var Handler $handler */
        $handler = $this->getContainer()->get(Handler::class);

        /**
         * Method invoke in SampleJob requires TaskInterface and it's available only in queue scope.
         */
        $handler->handle(SampleJob::class, '', '', '', '');
    }
}
