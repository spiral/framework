<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Command\Router;

use Spiral\Tests\Framework\ConsoleTestCase;

final class ListCommandTest extends ConsoleTestCase
{
    public function testRouteNamesAppearInOutput(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'auth',
            'scope',
            'intercepted:without',
        ]);
    }

    public function testAllVerbsShownAsWildcard(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['*']);
    }

    public function testControllerTargetFormat(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'Controller\AuthController->*',
        ]);
    }

    public function testActionTargetFormat(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'Controller\InterceptedController->without',
        ]);
    }

    public function testClosureTargetFormat(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'Closure(api.php:',
        ]);
    }

    public function testRouteGroupAppearsInOutput(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'api',
            'other',
        ]);
    }

    public function testPatternWithPrefixAppearsInOutput(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'api/test-import',
            'other/test-import',
        ]);
    }

    public function testGetVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['GET']);
    }

    public function testHeadVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['HEAD']);
    }

    public function testOptionsVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['OPTIONS']);
    }

    public function testPostVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['POST']);
    }

    public function testPatchVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['PATCH']);
    }

    public function testPutVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['PUT']);
    }

    public function testDeleteVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['DELETE']);
    }

    public function testLinkVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['LINK']);
    }

    public function testUnlinkVerbDisplay(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: ['UNLINK']);
    }
}
