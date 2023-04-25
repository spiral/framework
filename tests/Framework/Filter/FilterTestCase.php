<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Filter;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Tests\Framework\BaseTestCase;

abstract class FilterTestCase extends BaseTestCase
{
    /**
     * @template T of FilterInterface
     * @param class-string<T> $filter
     * @return T
     */
    public function getFilter(
        string $filter,
        array $post = [],
        array $query = [],
        array $headers = [],
        string $method = 'POST'
    ): FilterInterface {
        $request = new ServerRequest($method, '/');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $this->getContainer()->bind(
            ServerRequestInterface::class,
            $request->withParsedBody($post)->withQueryParams($query)
        );

        $input = $this->getContainer()->get(InputScope::class);

        return $this->getContainer()->get(FilterProviderInterface::class)
            ->createFilter($filter, $input);
    }
}
