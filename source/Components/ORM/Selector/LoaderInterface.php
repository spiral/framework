<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector;

use Spiral\Components\ORM\Selector;

interface LoaderInterface
{
    /**
     * Update loader options.
     *
     * @param array $options
     * @return static
     */
    public function setOptions(array $options = []);

    /**
     * Configure selector options.
     *
     * @param Selector $selector
     */
    public function configureSelector(Selector $selector);

    /**
     * Run post selection queries to clarify featched model data. Usually many conditions will be
     * fetched from there. Additionally this method may be used to create relations to external
     * source of data (ODM, elasticSearch and etc).
     */
    public function postLoad();

    /**
     * Reference key (from parent object) required to speed up data normalization. In most of cases
     * this is primary key of parent model.
     *
     * @return string
     */
    public function getReferenceKey();

    /**
     * Clean loader data.
     */
    public function clean();
}