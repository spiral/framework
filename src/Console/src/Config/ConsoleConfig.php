<?php

declare(strict_types=1);

namespace Spiral\Console\Config;

use Spiral\Console\Exception\ConfigException;
use Spiral\Console\Sequence\CallableSequence;
use Spiral\Console\Sequence\CommandSequence;
use Spiral\Console\SequenceInterface;
use Spiral\Core\InjectableConfig;

final class ConsoleConfig extends InjectableConfig
{
    public const CONFIG = 'console';

    protected array $config = [
        'name' => null,
        'version' => null,
        'commands' => [],
        'sequences' => [],
    ];

    public function getName(): string
    {
        return $this->config['name'] ?? 'Spiral Framework';
    }

    public function getVersion(): string
    {
        return $this->config['version'] ?? 'UNKNOWN';
    }

    /**
     * User defined set of commands (to be used when auto-location is off).
     */
    public function getCommands(): array
    {
        if (!\array_key_exists('commands', $this->config)) {
            //Legacy config support
            return [];
        }

        return $this->config['commands'];
    }

    /**
     * Get list of sequences with given name.
     *
     * @return \Generator|SequenceInterface[]
     *
     * @throws ConfigException
     */
    public function getSequence(string $name): \Generator
    {
        $sequence = (array)($this->config['sequences'][$name] ?? []);

        foreach ($sequence as $item) {
            yield $this->parseSequence($item);
        }
    }


    /**
     * Get list of configure sequences.
     *
     * @return \Generator|SequenceInterface[]
     *
     * @throws ConfigException
     */
    public function configureSequence(): \Generator
    {
        return $this->getSequence('configure');
    }

    /**
     * Get list of all update sequences.
     *
     * @return \Generator|SequenceInterface[]
     *
     * @throws ConfigException
     */
    public function updateSequence(): \Generator
    {
        return $this->getSequence('update');
    }

    /**
     * @throws ConfigException
     */
    protected function parseSequence(SequenceInterface|string|array $item): SequenceInterface
    {
        if ($item instanceof SequenceInterface) {
            return $item;
        }

        if (\is_callable($item)) {
            return new CallableSequence($item);
        }

        if (\is_array($item) && isset($item['command'])) {
            return new CommandSequence(
                $item['command'],
                $item['options'] ?? [],
                $item['header'] ?? '',
                $item['footer'] ?? ''
            );
        }

        if (\is_array($item) && isset($item['invoke'])) {
            return new CallableSequence(
                $item['invoke'],
                $item['header'] ?? '',
                $item['footer'] ?? ''
            );
        }

        throw new ConfigException(
            \sprintf(
                'Unable to parse sequence `%s`.',
                \json_encode($item, JSON_THROW_ON_ERROR)
            )
        );
    }
}
