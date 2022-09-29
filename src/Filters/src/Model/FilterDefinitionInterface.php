<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

interface FilterDefinitionInterface
{
    /**
     * The core of any filter object is schema; ce:origin or field => source. The source is the subset of data
     * from user input. In the HTTP scope, the sources can be cookie, data, query, input (data+query), header, file,
     * server. The origin is the name of the external field (dot notation is supported).
     *
     * Example schema definition:this method defines mapping between fields and values provided by
     * input. Every key pair is defined as field => sour
     *
     * return [
     *       // identical to "data:name"
     *      'name'   => 'post:name',
     *
     *       // field name will be used as search criteria in a query ("query:field")
     *      'field'  => 'query',
     *
     *       // Yep, that's too
     *      'file'   => 'file:images.preview',
     *
     *       // Alias for InputManager->isSecure(),
     *      'secure' => 'isSecure'
     *
     *       // Iterate over file:uploads array with model UploadFilter and isolate it in uploads.*
     *      'uploads' => [UploadFilter::class, "uploads.*", "file:upload"],
     *
     *      // Nested model associated with address subset of data
     *      'address' => AddressRequest::class,
     *
     *       // Identical to previous definition
     *      'address' => [AddressRequest::class, "address"]
     * ];
     *
     * You can declare as source (query, file, post and e.t.c) as source plus origin name (file:files.0).
     * Available sources: uri, path, method, isSecure, isAjax, isJsonExpected, remoteAddress.
     * Plus named sources (bags): header, data, post, query, cookie, file, server, attribute.
     */
    public function mappingSchema(): array;
}
