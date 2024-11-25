<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Fixtures;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Boot\Bootloader\Bootloader;

final class UserContextBootloader extends Bootloader
{
    public function defineBindings(): array
    {
        return [
            UserContext::class => [self::class, 'userContext'],
        ];
    }

    private function userContext(ServerRequestInterface $request): UserContext
    {
        return isset($request->getQueryParams()['context'])
            ? UserContext::create()
            : throw new \Exception(
                'Unable to resolve UserContext, invalid request.',
            );
    }
}
