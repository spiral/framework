<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface ExtendedOptionsInterface extends OptionsInterface
{
    /**
     * @return array<non-empty-string, array<string>>
     */
    public function getHeaders(): array;

    /**
     * @param non-empty-string $name Header field name.
     */
    public function hasHeader(string $name): bool;

    /**
     * @param non-empty-string $name
     *
     * @return array<string>
     */
    public function getHeader(string $name): array;

    /**
     * @param non-empty-string $name
     */
    public function getHeaderLine(string $name): string;

    /**
     * @param non-empty-string $name
     * @param non-empty-string|array<non-empty-string> $value
     */
    public function withHeader(string $name, string|array $value): self;

    /**
     * @param non-empty-string $name
     * @param non-empty-string|array<non-empty-string> $value
     */
    public function withAddedHeader(string $name, string|array $value): self;

    /**
     * @param non-empty-string $name
     */
    public function withoutHeader(string $name): self;
}
