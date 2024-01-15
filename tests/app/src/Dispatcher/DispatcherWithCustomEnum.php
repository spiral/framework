<?php

declare(strict_types=1);

namespace Spiral\App\Dispatcher;

use Spiral\Attribute\DispatcherScope;

#[DispatcherScope(scope: Scope::Custom)]
final class DispatcherWithCustomEnum extends AbstractDispatcher
{
}
