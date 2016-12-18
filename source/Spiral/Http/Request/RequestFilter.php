<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Http\Request;

use Spiral\Http\Exceptions\InputException;
use Spiral\Models\ValidatesEntity;
use Spiral\Validation\ValidatorInterface;

/**
 * Request filter is data entity which uses input manager to populate it's fields, model can
 * perform input filtering, value routing (query, data, files) and validation.
 *
 * Attention, you can not inherit one request from another at this moment. You can use generic
 * validation rules for your input fields.
 *
 * Please do not request instance without using container, constructor signature might change over
 * time (or another request filter class can be created with inheritance and composition support).
 *
 * Example schema definition:
 * const SCHEMA = [
 *      'name'   => 'post:name',           //identical to "data:name"
 *      'field'  => 'query',               //field name will used as search criteria in query
 *      'file'   => 'file:images.preview', //Yep, that's too
 *      'secure' => 'isSecure'             //Alias for InputManager->isSecure()
 * ];
 *
 * You can declare as source (query, file, post and etc) as source plus origin name (file:files.0).
 * Available sources: uri, path, method, isSecure, isAjax, isJsonExpected, remoteAddress.
 * Plus named sources (bags): header, data, post, query, cookie, file, server, attribute.
 *
 * @todo HIERARCHICAL VALIDATION
 */
class RequestFilter extends ValidatesEntity
{
    /**
     * Defines request data mapping (input => request property)
     */
    const SCHEMA = [];

    /**
     * @param InputInterface     $input
     * @param ValidatorInterface $validator
     */
    public function __construct(InputInterface $input = null, ValidatorInterface $validator = null)
    {
        //We are going to populate input fields manually
        parent::__construct([], $validator);

        if (empty(static::SCHEMA)) {
            throw new InputException("Unable to initiate RequestFilter with empty schema");
        }

        if (!empty($input)) {
            $this->initValues($input);
        }
    }

    /**
     * Set request values based on a given input interface.
     *
     * @param InputInterface $input
     */
    public function initValues(InputInterface $input)
    {

        foreach ($this->schema as $field => $source) {
            list($source, $origin) = $this->parseSource($field, $source);

            //Getting data from input source
            $this->setField($field, $input->getValue($source, $origin), true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors($reset = true)
    {
        //De-mapping
        $errors = [];
        foreach (parent::getErrors($reset) as $field => $errorSet) {
            list(, $origin) = $this->parseSource($field, $this->schema[$field]);

            if ($field == $origin) {
                $errors[$field] = $errorSet;
            } else {
                //Let's recreate original structure
                $this->dotSet($errors, $origin, $errorSet);
            }
        }

        return $errors;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'schema' => $this->schema,
            'fields' => $this->getFields(),
            'errors' => $this->getErrors()
        ];
    }

    /**
     * Fetch source name and origin from schema definition.
     *
     * @param string $field
     * @param string $source
     *
     * @return array [$source, $origin]
     */
    private function parseSource($field, $source)
    {
        if (strpos($source, ':') === false) {
            return [$source, $field];
        }

        return explode(':', $source);
    }

    /**
     * Set element using dot notation.
     *
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     */
    private function dotSet(array &$array, $path, $value)
    {
        $step = explode('.', $path);
        while ($name = array_shift($step)) {
            $array = &$array[$name];
        }

        $array = $value;
    }
}