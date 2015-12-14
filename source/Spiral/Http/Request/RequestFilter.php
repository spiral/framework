<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Request;

use Spiral\Models\DataEntity;

/**
 * Request filter is data entity which uses input manager to populate it's fields, model can
 * perform
 * input filtering, value routing (query, data, files) and filtering.
 *
 * Attention, you can not inherit one request from another at this moment. You can use generic
 * validation rules for your input fields.
 * Please do not request instance without using container, constructor signature might change over
 * time (or another request filter class can be created with inheritance and composition support).
 *
 * Example schema definition:
 * protected $schema = [
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
 * @todo There is possibility that this class and it's schema will behave same way as ORM and
 * @todo ODM models one day.
 */
class RequestFilter extends DataEntity
{
    /**
     * Request filter makes every field settable.
     *
     * @var array
     */
    protected $secured = [];

    /**
     * Request schema declares field names, their source and origin name if any. See examples in
     * class header.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * @invisible
     * @var InputInterface
     */
    protected $input = null;

    /**
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        parent::__construct([]);
        $this->input = $input;

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
     * @return object
     */
    public function __debugInfo()
    {
        return (object)[
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
