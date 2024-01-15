<?php

declare(strict_types=1);

namespace Spiral\App\Dispatcher;

use Spiral\Attribute\DispatcherScope;
use Spiral\Framework\ScopeName;

#[DispatcherScope(scope: ScopeName::Console)]
final class DispatcherWithScopeName extends AbstractDispatcher
{
}
