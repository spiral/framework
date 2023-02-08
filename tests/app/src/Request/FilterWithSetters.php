<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Model\Filter;

class FilterWithSetters extends Filter
{
    #[Post]
    #[Setter(filter: 'intval')]
    public int $integer;

    #[Post]
    #[Setter(filter: 'strval')]
    #[Setter('ltrim', '-')]
    #[Setter('rtrim', ' ')]
    #[Setter('htmlspecialchars')]
    public string $string;
}
