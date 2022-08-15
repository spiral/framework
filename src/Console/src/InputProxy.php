<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Provides the ability to inject desired command name into Symfony\Console\Application->doRun();
 */
final class InputProxy implements InputInterface
{
    /** @var InputInterface */
    private $input;

    /** @var array */
    private $overwrite;

    public function __construct(InputInterface $input, array $overwrite)
    {
        $this->input = $input;
        $this->overwrite = $overwrite;
    }

    /**
     * @inheritDoc
     */
    public function getFirstArgument(): ?string
    {
        return $this->overwrite['firstArgument'] ?? $this->input->getFirstArgument();
    }

    /**
     * @inheritDoc
     */
    public function hasParameterOption($values, $onlyParams = false): bool
    {
        return $this->input->hasParameterOption($values, $onlyParams = false);
    }

    /**
     * @inheritDoc
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default = false, $onlyParams = false);
    }

    /**
     * @inheritDoc
     */
    public function bind(InputDefinition $definition)
    {
        return $this->input->bind($definition);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        return $this->input->validate();
    }

    /**
     * @inheritDoc
     */
    public function getArguments(): array
    {
        return $this->input->getArguments();
    }

    /**
     * @inheritDoc
     */
    public function getArgument($name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * @inheritDoc
     */
    public function setArgument($name, $value)
    {
        return $this->input->setArgument($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function hasArgument($name): bool
    {
        return $this->input->hasArgument($name);
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        return $this->input->getOptions();
    }

    /**
     * @inheritDoc
     */
    public function getOption($name)
    {
        return $this->input->getOption($name);
    }

    /**
     * @inheritDoc
     */
    public function setOption($name, $value)
    {
        return $this->input->setOption($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function hasOption($name): bool
    {
        return $this->input->hasOption($name);
    }

    /**
     * @inheritDoc
     */
    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    /**
     * @inheritDoc
     */
    public function setInteractive($interactive)
    {
        return $this->input->setInteractive($interactive);
    }
}
