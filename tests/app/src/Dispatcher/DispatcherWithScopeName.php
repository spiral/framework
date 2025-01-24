<?php

declare(strict_types=1);

namespace Spiral\App\Dispatcher;

use Spiral\Attribute\DispatcherScope;
use Spiral\Framework\Spiral;

#[DispatcherScope(scope: Spiral::Console)]
final class DispatcherWithScopeName extends AbstractDispatcher {}
