<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Filter;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Tests\Framework\BaseTest;

abstract class FilterTestCase extends BaseTest
{
    /**
     * @param class-string<FilterInterface> $filter
     */
    public function getFilter(
        string $filter,
        array $post = [],
        array $query = [],
        array $headers = []
    ): FilterInterface {
        $request = new ServerRequest('POST', '/');

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
