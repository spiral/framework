<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Filters;

use Spiral\Console\Command;
use Spiral\Filters\FilterMapper;
use Spiral\Filters\LocatorInterface;

class UpdateCommand extends Command
{
    const NAME = 'filter:update';
    const DESCRIPTION = 'Update available filters mapping schema';

    /**
     * @param FilterMapper     $mapper
     * @param LocatorInterface $locator
     */
    public function perform(FilterMapper $mapper, LocatorInterface $locator)
    {
        $builder = $mapper->buildSchema($locator);
        $filters = array_keys($builder->buildSchema());

        if ($this->isVerbose()) {
            foreach ($filters as $name) {
                $this->sprintf("<info>[filter]</info> %s\n", $name);
            }
        }

        $mapper->setSchema($builder, true);
        $this->sprintf("Schema has been updated, <comment>%s</comment> filter(s) found.\n", count($filters));
    }
}