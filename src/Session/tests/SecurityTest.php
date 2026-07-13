<?php

declare(strict_types=1);

namespace Spiral\Tests\Session;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Files\Files;
use Spiral\Session\Handler\FileHandler;
use Spiral\Session\Session;

final class SecurityTest extends TestCase
{
    private string $directory;

    public static function maliciousIds(): iterable
    {
        yield 'parent traversal' => ['../../public/shell.php'];
        yield 'single traversal' => ['../evil.txt'];
        yield 'nested slash' => ['foo/bar'];
        yield 'leading slash (absolute)' => ['/etc/passwd'];
        yield 'null byte' => ["ok\0../evil"];
        yield 'newline' => ["ok\n../evil"];
    }

    /**
     * The id allowlist must reject any id that leaves the [-,a-zA-Z0-9] character class.
     * On the vulnerable code preg_match(...) !== false is true for 0, so the traversal id
     * is accepted and getID() returns it verbatim.
     */
    #[DataProvider('maliciousIds')]
    public function testValidIdRejectsMaliciousSessionId(string $maliciousId): void
    {
        $session = new Session('sig', 86400, $maliciousId);

        self::assertNull(
            $session->getID(),
            \sprintf('Session accepted invalid id %s; the id allowlist is inert.', \var_export($maliciousId, true)),
        );
    }

    /**
     * A legitimate id (only [-,a-zA-Z0-9]) must still be accepted after the fix.
     */
    public function testValidIdStillAcceptsLegitimateSessionId(): void
    {
        $id = 'abc-123,DEF';
        $session = new Session('sig', 86400, $id);

        self::assertSame($id, $session->getID());
    }

    /**
     * The default file handler must never write outside its configured directory, even when it
     * is handed a crafted id. On the vulnerable code the file lands in the parent directory.
     */
    public function testFileHandlerDoesNotEscapeSessionDirectory(): void
    {
        $files = new Files();
        $sessionDir = $this->directory . '/runtime/session';
        $files->ensureDirectory($sessionDir);

        $handler = new FileHandler($files, $sessionDir);

        $handler->write('../../public/shell.php', '<?php echo "pwned"; ?>');

        $escaped = $this->directory . '/public/shell.php';
        self::assertFileDoesNotExist(
            $escaped,
            'FileHandler wrote outside the session directory - path traversal is possible.',
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = \sys_get_temp_dir() . '/spiral_session_security_' . \getmypid() . '_' . \uniqid();
        \mkdir($this->directory, 0777, true);
    }

    protected function tearDown(): void
    {
        (new Files())->deleteDirectory($this->directory, true);
        if (\is_dir($this->directory)) {
            @\rmdir($this->directory);
        }
    }
}
