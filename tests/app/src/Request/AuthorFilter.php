<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class AuthorFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    #[Setter('intval')]
    public int $id;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition([
            'id' => ['integer', 'required'],
        ]);
    }
}
