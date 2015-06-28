<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models;

interface DatabaseEntityInterface
{
    /**
     * Set value to one of field. Setter filter can be disabled by providing last argument.
     *
     * @param string $name   Field name.
     * @param mixed  $value  Value to set.
     * @param bool   $filter If false no filter will be applied.
     */
    public function setField($name, $value, $filter = true);

    /**
     * Update multiple non-secured model fields.
     *
     * @param array $fields
     * @return static
     */
    public function setFields($fields = []);

    /**
     * Get one specific field value and apply getter filter to it. You can disable getter filter by
     * providing second argument.
     *
     * @param string $name    Field name.
     * @param mixed  $default Default value to return if field not set.
     * @param bool   $filter  If false no filter will be applied.
     * @return mixed
     */
    public function getField($name, $default = null, $filter = true);

    /**
     * Get all models fields.
     *
     * @param bool $filter Apply getters.
     * @return array
     */
    public function getFields($filter = true);

    /**
     * Create new model and set it's fields, all field values will be passed thought model filters
     * to ensure their type.
     *
     * @param array $fields Model fields to set, will be passed thought filters.
     * @return static
     */
    public static function create($fields = []);

    /**
     * Is model were fetched from databases or recently created? Usually checks primary key value.
     *
     * @return bool
     */
    public function isLoaded();

    /**
     * Check if model or specific field was changed since fetching model from database.
     *
     * @param string $field
     * @return bool
     */
    public function hasUpdates($field = null);

    /**
     * Save model to related database. Model validation has to be performed before saving. Method
     * should return true is model was successfully saved or false if validation/saving error occure.
     *
     * @return bool
     */
    public function save();

    /**
     * Delete model from related database.
     */
    public function delete();
}