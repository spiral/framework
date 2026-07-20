<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions\Fixtures;

use Spiral\Exceptions\Attribute\NonReportable;

#[NonReportable]
final class TestException extends \Exception {}
