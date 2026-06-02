<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Command\Router;

use Spiral\Tests\Framework\ConsoleTestCase;

final class ListCommandTest extends ConsoleTestCase
{
    public function testRouteNamesAppearInOutput(): void
    {
        $output = $this->runCommand('route:list');

        // Anchor each name to the Name column (preceded by a column border, followed by padding + border)
        // so that e.g. `intercepted:without` is not matched inside `intercepted:withoutAttribute`.
        $this->assertMatchesRegularExpression('/\|\s+auth\s+\|/', $output);
        $this->assertMatchesRegularExpression('/\|\s+scope\s+\|/', $output);
        $this->assertMatchesRegularExpression('/\|\s+intercepted:without\s+\|/', $output);
    }

    public function testAllVerbsShownAsWildcard(): void
    {
        // A route accepting all verbs renders `*` as its own Verbs cell (between two borders),
        // distinct from target wildcards such as `AuthController->*`.
        $this->assertMatchesRegularExpression('/\|\s+\*\s+\|/', $this->runCommand('route:list'));
    }

    public function testControllerTargetFormat(): void
    {
        // `auth` route -> Controller target rendered as `Controller\AuthController->*` in the Target cell.
        $this->assertMatchesRegularExpression(
            '/\\\\AuthController->\*\s+\|/',
            $this->runCommand('route:list'),
        );
    }

    public function testActionTargetFormat(): void
    {
        // `intercepted:without` -> Action target. Anchor to the cell border so it is not matched
        // inside `InterceptedController->withoutAttribute`.
        $this->assertMatchesRegularExpression(
            '/\\\\InterceptedController->without\s+\|/',
            $this->runCommand('route:list'),
        );
    }

    public function testClosureTargetFormat(): void
    {
        $this->assertMatchesRegularExpression(
            '/Closure\(api\.php:\d+\)/',
            $this->runCommand('route:list'),
        );
    }

    public function testRouteGroupAppearsInOutput(): void
    {
        $output = $this->runCommand('route:list');

        // Tie the group value to a concrete route row (name ... | group |) so an empty/wrong
        // Group column cannot pass just because `api`/`other` appear in patterns or targets.
        $this->assertMatchesRegularExpression('/\|\s+api\.test-import-index\s+\|.*\|\s*api\s+\|/', $output);
        $this->assertMatchesRegularExpression('/\|\s+test-import-index\s+\|.*\|\s*other\s+\|/', $output);
    }

    public function testPatternWithPrefixAppearsInOutput(): void
    {
        $output = $this->runCommand('route:list');

        // Anchor to the Pattern cell so the prefixed index pattern is not matched inside the longer
        // `api/test-import/posts` pattern.
        $this->assertMatchesRegularExpression('/\|\s+api\/test-import\s+\|/', $output);
        $this->assertMatchesRegularExpression('/\|\s+other\/test-import\s+\|/', $output);
    }

    public function testGetVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-get', 'GET');
    }

    public function testHeadVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-head', 'HEAD');
    }

    public function testOptionsVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-options', 'OPTIONS');
    }

    public function testPostVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-post', 'POST');
    }

    public function testPatchVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-patch', 'PATCH');
    }

    public function testPutVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-put', 'PUT');
    }

    public function testDeleteVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-delete', 'DELETE');
    }

    public function testLinkVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-link', 'LINK');
    }

    public function testUnlinkVerbDisplay(): void
    {
        $this->assertVerbForRoute('verb-unlink', 'UNLINK');
    }

    /**
     * Assert that the dedicated `$name` route renders `$verb` in its Verbs column (the cell directly
     * after the Name cell), so the verb rendering is actually exercised for that route rather than
     * matching the same verb emitted by some unrelated route elsewhere in the table.
     */
    private function assertVerbForRoute(string $name, string $verb): void
    {
        $this->assertMatchesRegularExpression(
            \sprintf('/\|\s+%s\s+\|\s+%s\b/', \preg_quote($name, '/'), \preg_quote($verb, '/')),
            $this->runCommand('route:list'),
        );
    }
}
