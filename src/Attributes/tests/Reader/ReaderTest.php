<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader;

use Spiral\Attributes\ReaderInterface;
use Spiral\Tests\Attributes\TestCase;

abstract class ReaderTest extends TestCase
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->reader = $this->create();
    }

    /**
     * @return ReaderInterface
     */
    abstract protected function create(): ReaderInterface;
}
