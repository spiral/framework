<?php

declare(strict_types=1);

namespace Spiral\Console;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Provides the ability to inject desired command name into Symfony\Console\Application->doRun();
 */
final class InputProxy implements InputInterface
{
    public function __construct(
        private readonly InputInterface $input,
        private readonly array $overwrite
    ) {
    }

    public function __toString(): string
    {
        return $this->input->__toString();
    }

    public function getFirstArgument(): ?string
    {
        return $this->overwrite['firstArgument'] ?? $this->input->getFirstArgument();
    }

    public function hasParameterOption(string|array $values, bool $onlyParams = false): bool
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    public function getParameterOption(
        string|array $values,
        string|bool|int|float|array|null $default = false,
        bool $onlyParams = false
    ): mixed {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    public function bind(InputDefinition $definition): void
    {
        $this->input->bind($definition);
    }

    public function validate(): void
    {
        $this->input->validate();
    }

    public function getArguments(): array
    {
        return $this->input->getArguments();
    }

    public function getArgument(string $name): mixed
    {
        return $this->input->getArgument($name);
    }

    public function setArgument(string $name, mixed $value): void
    {
        $this->input->setArgument($name, $value);
    }

    public function hasArgument(string $name): bool
    {
        return $this->input->hasArgument($name);
    }

    public function getOptions(): array
    {
        return $this->input->getOptions();
    }

    public function getOption(string $name): mixed
    {
        return $this->input->getOption($name);
    }

    public function setOption(string $name, mixed $value): void
    {
        $this->input->setOption($name, $value);
    }

    public function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    public function setInteractive(bool $interactive): void
    {
        $this->input->setInteractive($interactive);
    }
}
