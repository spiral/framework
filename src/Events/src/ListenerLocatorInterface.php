<?php

declare(strict_types=1);

namespace Spiral\Events;

use Spiral\Events\Attribute\Listener;

interface ListenerLocatorInterface
{
    /**
     * @return \Iterator<class-string, Listener>
     */
    public function findListeners(): \Iterator;
}
