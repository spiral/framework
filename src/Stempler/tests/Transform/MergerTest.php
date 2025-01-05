<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Transform\Import\Bundle;
use Spiral\Stempler\Transform\Merger;

final class MergerTest extends TestCase
{
    public function testMergeWithBundle(): void
    {
        $merger = new Merger();

        $template = $merger->merge(new Template([new Bundle('/')]), new Tag());

        self::assertCount(1, $template->nodes);
    }
}
