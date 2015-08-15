<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http;

use Spiral\Http\Exceptions\FilterException;
use Spiral\Models\DataEntity;

/**
 * Request filter is data entity which uses input manager to populate it's fields, model can perform
 * input filtering, value routing (query, data, files) and filtering.
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
     * Request schema declares field names, their source and origin name if any. See examples in class
     * header.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * @final For my own reasons (i have some ideas), please use SaturableInterface and init method.
     * @param InputManager $input
     */
    final public function __construct(InputManager $input)
    {
        foreach ($this->schema as $field => $source) {
            list($source, $origin) = $this->parseSource($field, $source);

            if (!method_exists($input, $source)) {
                throw new FilterException("Undefined source '{$source}'.");
            }

            //Receiving value as result of InputManager method
            $this->setField($field, call_user_func([$input, $source], $origin), true);
        }
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
}