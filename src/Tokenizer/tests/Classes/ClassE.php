<?php

namespace Spiral\Tests\Tokenizer\Classes;

enum ClassE: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}