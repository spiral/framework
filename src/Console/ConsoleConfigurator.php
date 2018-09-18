<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console;

use Spiral\Config\ModifierInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Console\Sequence\CallableSequence;
use Spiral\Console\Sequence\CommandSequence;

class ConsoleConfigurator
{
    /** @var ModifierInterface */
    private $modifier;

    /** @var string */
    private $config;

    /**
     * @param ModifierInterface $modifier
     * @param string            $config
     */
    public function __construct(ModifierInterface $modifier, string $config = 'console')
    {
        $this->modifier = $modifier;
        $this->config = $config;
    }

    /**
     * @param string $command
     */
    public function addCommand(string $command)
    {
        $this->modifier->modify($this->config, new AppendPatch('commands', null, $command));
    }

    /**
     * @param array|string $sequence
     * @param string       $header
     * @param string       $footer
     * @param array        $options
     */
    public function configureSequence($sequence, string $header, string $footer = '', array $options = [])
    {
        $this->modifier->modify(
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
        $this->modifier->modify(
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