<?php

declare(strict_types=1);

namespace Spiral\App\SomeService;

use Spiral\Prototype\Annotation\Prototyped;

#[Prototyped(property: 'service.client')]
class Client
{

}
