<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

interface RelationInterface
{
    public function __construct(ORM $orm, ActiveRecord $parent, array $definition, $data = null);

    public function saveContent($validate = true);

    public function getData();

    public function setData($data);
}