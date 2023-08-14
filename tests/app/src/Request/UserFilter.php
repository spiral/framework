<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

class UserFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    public string $name;

    #[NestedFilter(class: PostFilter::class)]
    public PostFilter $postFilter;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition(validationRules: [
            'name' => ['required', 'string'],
        ]);
    }
}
