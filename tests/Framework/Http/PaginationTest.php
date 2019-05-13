<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Http;

use Spiral\Framework\HttpTest;

class PaginationTest extends HttpTest
{
    public function testPaginate()
    {
        $this->assertSame('1', (string)$this->get('/paginate', [

        ])->getBody());
    }

    public function testPaginate2()
    {
        $this->assertSame('2', (string)$this->get('/paginate', [
            'page' => 2
        ])->getBody());
    }
}