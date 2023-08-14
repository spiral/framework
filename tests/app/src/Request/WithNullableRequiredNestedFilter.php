<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class WithNullableRequiredNestedFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    public string $name;

    #[NestedFilter(class: AddressFilter::class)]
    public ?AddressFilter $address = null;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition(validationRules: [
            'name' => ['required', 'string'],
            'address' => ['required'],
        ]);
    }
}
