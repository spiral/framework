<?php
/**
 * Validator configuration. Attention, configs might include runtime code which depended on
 * environment values only.
 *
 * @see ValidatorConfig
 */
use Spiral\Validation\Checkers;

return [
    /*
     * Set of empty conditions which tells Validator what rules to be counted as "stop if empty",
     * without such condition field validations will be skipped if value is empty.
     */
    'emptyConditions' => [
        "notEmpty",
        "required",
        "type::notEmpty",
        "required::with",
        "required::without",
        "required::withAll",
        "required::withoutAll",
        "file::exists",
        "file::uploaded",
        "image:valid"
        /*{{empties}}*/
    ],

    /*
     * Checkers are resolved using container and provide ability to isolate some validation rules
     * under common name and class. You can register new checkers at any moment without any
     * performance issues.
     */
    'checkers'        => [
        "type"     => Checkers\TypeChecker::class,
        "required" => Checkers\RequiredChecker::class,
        "number"   => Checkers\NumberChecker::class,
        "mixed"    => Checkers\MixedChecker::class,
        "address"  => Checkers\AddressChecker::class,
        "string"   => Checkers\StringChecker::class,
        "file"     => Checkers\FileChecker::class,
        "image"    => Checkers\ImageChecker::class,
        /*{{checkers}}*/
    ],

    /*
     * Aliases are only used to simplify developer life.
     */
    'aliases'         => [
        "notEmpty"   => "type::notEmpty",
        "required"   => "type::notEmpty",
        "datetime"   => "type::datetime",
        "timezone"   => "type::timezone",
        "bool"       => "type::boolean",
        "boolean"    => "type::boolean",
        "cardNumber" => "mixed::cardNumber",
        "regexp"     => "string::regexp",
        "email"      => "address::email",
        "url"        => "address::url",
        "file"       => "file::exists",
        "uploaded"   => "file::uploaded",
        "filesize"   => "file::size",
        "image"      => "image::valid",
        "array"      => "is_array",
        "callable"   => "is_callable",
        "double"     => "is_double",
        "float"      => "is_float",
        "int"        => "is_int",
        "integer"    => "is_integer",
        "numeric"    => "is_numeric",
        "long"       => "is_long",
        "null"       => "is_null",
        "object"     => "is_object",
        "real"       => "is_real",
        "resource"   => "is_resource",
        "scalar"     => "is_scalar",
        "string"     => "is_string",
        "match"      => "mixed::match",
        /*{{aliases}}*/
    ]
];
