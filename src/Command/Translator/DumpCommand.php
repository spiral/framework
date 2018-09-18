<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Translator;

use Spiral\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DumpCommand extends Command
{
    const NAME = "i18n:dump";
    const DESCRIPTION = 'Dump given locale using specified dumper and path';

    const ARGUMENTS = [
        ['locale', InputArgument::REQUIRED, 'Locale to be dumped'],
        ['path', InputArgument::REQUIRED, 'Export path']
    ];

    const OPTIONS = [
        ['dumper', 'd', InputOption::VALUE_OPTIONAL, 'Dumper name', 'php'],
        ['fallback', 'f', InputOption::VALUE_NONE, 'Merge messages from fallback locale'],
    ];

}