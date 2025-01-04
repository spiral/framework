<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Directive;

use Spiral\Stempler\Directive\LoopDirective;

class LoopTest extends BaseTestCase
{
    protected const DIRECTIVES = [
        LoopDirective::class
    ];

    public function testForeachEndForeach(): void
    {
        $doc = $this->parse('@foreach($users as $u) {{ $u->name }} @endforeach');

        self::assertSame('<?php foreach($users as $u): ?> <?php echo htmlspecialchars'
        . "((string) (\$u->name), ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8'); ?> <?php endforeach; ?>", $this->compile($doc));
    }

    public function testWhileEndWhile(): void
    {
        $doc = $this->parse('@while(true) {!! "OK" !!} @endwhile');

        self::assertSame('<?php while(true): ?> <?php echo "OK"; ?> <?php endwhile; ?>', $this->compile($doc));
    }

    public function testForEndFor(): void
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) {!! $i !!} @endfor');

        self::assertSame('<?php for($i=0; $i<100; $i++): ?> <?php echo $i; ?> <?php endfor; ?>', $this->compile($doc));
    }

    public function testBreak(): void
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) @break @endfor');

        self::assertSame('<?php for($i=0; $i<100; $i++): ?> <?php break; ?> <?php endfor; ?>', $this->compile($doc));
    }

    public function testBreak2(): void
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) @break(2) @endfor');

        self::assertSame('<?php for($i=0; $i<100; $i++): ?> <?php break 2; ?> <?php endfor; ?>', $this->compile($doc));
    }

    public function testContinue(): void
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) @continue @endfor');

        self::assertSame('<?php for($i=0; $i<100; $i++): ?> <?php continue; ?> <?php endfor; ?>', $this->compile($doc));
    }

    public function testContinue2(): void
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) @continue(2) @endfor');

        self::assertSame('<?php for($i=0; $i<100; $i++): ?> <?php continue 2; ?> <?php endfor; ?>', $this->compile($doc));
    }
}
