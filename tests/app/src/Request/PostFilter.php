<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class PostFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    #[Setter('strval')]
    public string $body;

    #[Post]
    #[Setter('intval')]
    public int $revision;

    #[Post]
    #[Setter('boolval')]
    public bool $active;

    #[Post(key: 'post_rating')]
    #[Setter('floatval')]
    public float $postRating;

    #[NestedFilter(AuthorFilter::class)]
    public AuthorFilter $author;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition([
            'body' => ['string', 'required'],
            'revision' => ['integer', 'required'],
            'active' => ['boolean', 'required'],
            'postRating' => ['float', 'required'],
        ]);
    }
}
