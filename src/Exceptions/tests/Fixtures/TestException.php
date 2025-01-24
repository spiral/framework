<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions\Fixtures;

use Spiral\Exceptions\Attribute\NonReportable;

#[NonReportable]
class TestException extends \Exception {}
