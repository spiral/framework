<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class MultipleAddressesFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    public string $name;

    #[NestedArray(class: AddressFilter::class, input: new Post('addresses'))]
    public array $addresses;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition(validationRules: [
            'name' => ['required', 'string'],
        ]);
    }
}
