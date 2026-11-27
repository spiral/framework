<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;

class WideLeafFilter extends Filter
{
    #[Post]
    public mixed $a = null;

    #[Post]
    public mixed $b = null;

    #[Post]
    public mixed $c = null;

    #[Post]
    public mixed $d = null;

    #[Post]
    public mixed $e = null;

    #[Post]
    public mixed $f = null;

    #[Post]
    public mixed $g = null;

    #[Post]
    public mixed $h = null;

    #[Post]
    public mixed $i = null;

    #[Post]
    public mixed $j = null;
}
