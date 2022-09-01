<?php

declare(strict_types=1);

namespace MonorepoBuilder;

interface TagParserInterface
{
    public function parse(string $gitDirectory): array;
}
