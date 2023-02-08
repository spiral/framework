<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

class AddressFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    public string $city;

    #[Post]
    public string $address;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition(validationRules: [
            'city' => ['required', 'string'],
            'address' => ['required', 'string'],
        ]);
    }
}
