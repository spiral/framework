<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Console\Sequence\CallableSequence;
use Spiral\Console\Sequence\CommandSequence;

class ConsoleConfigurator
{
    /** @var ConfiguratorInterface */
    private $configurator;

    /** @var string */
    private $config;

    /**
     * @param ConfiguratorInterface $configurator
     * @param string                $config
     */
    public function __construct(ConfiguratorInterface $configurator, string $config = 'console')
    {
        $this->configurator = $configurator;
        $this->config = $config;
    }

    /**
     * @param string $command
     */
    public function addCommand(string $command)
    {
        $this->configurator->modify($this->config, new AppendPatch('commands', null, $command));
    }

    /**
     * @param array|string $sequence
     * @param string       $header
     * @param string       $footer
     * @param array        $options
     */
    public function configureSequence($sequence, string $header, string $footer = '', array $options = [])
    {
        $this->configurator->modify(
            $this->config,
            $this->sequencePatch('configure', $sequence, $header, $footer, $options)
        );
    }

    /**
     * @param array|string $sequence
     * @param string       $header
     * @param string       $footer
     * @param array        $options
     */
    public function updateSequence($sequence, string $header, string $footer = '', array $options = [])
    {
        $this->configurator->modify(
            $this->config,
            $this->sequencePatch('update', $sequence, $header, $footer, $options)
        );
    }

    /**
     * @param string $target
     * @param mixed  $sequence
     * @param string $header
     * @param string $footer
     * @param array  $options
     * @return AppendPatch
     */
    private function sequencePatch(string $target, $sequence, string $header, string $footer, array $options)
    {
        if (is_array($sequence)) {
            return new AppendPatch($target, null, new CallableSequence($sequence, $options, $header, $footer));
        }

        return new AppendPatch($target, null, new CommandSequence($sequence, $options, $header, $footer));
    }
}