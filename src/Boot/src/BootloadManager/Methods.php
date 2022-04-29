<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

enum Methods: string
{
    case INIT = 'init';
    case BOOT = 'boot';
}
