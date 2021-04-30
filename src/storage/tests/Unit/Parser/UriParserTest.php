<?php

namespace Spiral\Tests\Storage\Unit\Parser;

use Spiral\Storage\Exception\UriException;
use Spiral\Storage\Parser\Uri;
use Spiral\Tests\Storage\Unit\UnitTestCase;

class UriParserTest extends UnitTestCase
{
    /**
     * @dataProvider getUriList
     *
     * @param string $fs
     * @param string $path
     * @param string $uri
     * @throws UriException
     */
    public function testPrepareUri(string $fs, string $path, string $uri): void
    {
        $uriStructure = Uri::create($fs, $path);

        $this->assertEquals($fs, $uriStructure->getFileSystem());
        $this->assertEquals($path, $uriStructure->getPath());
        $this->assertEquals($uri, (string)$uriStructure);
    }

    /**
     * @dataProvider getUriList
     *
     * @param string $fs
     * @param string $path
     * @param string $uri
     *
     * @throws UriException
     */
    public function testParseUri(string $fs, string $path, string $uri): void
    {
        $uriStructure = $this->getUriParser()->parse($uri);

        $this->assertEquals($fs, $uriStructure->getFileSystem());
        $this->assertEquals($path, $uriStructure->getPath());
        $this->assertEquals($uri, (string)$uriStructure);
    }

    /**
     * @dataProvider getBadUriList
     *
     * @param string $uri
     * @param string $expectedMsg
     *
     * @throws UriException
     */
    public function testParseUriThrowsException(string $uri, string $expectedMsg): void
    {
        $this->expectException(UriException::class);
        $this->expectExceptionMessage($expectedMsg);

        $this->getUriParser()->parse($uri);
    }

    /**
     * @dataProvider getUriListWithSeparators
     *
     * @param string $fs
     * @param string $path
     * @param string $uri
     *
     * @throws \ReflectionException
     */
    public function testBuildUriStructure(string $fs, string $path, string $uri): void
    {
        $uriStructure = Uri::create($fs, $path);

        $this->assertEquals($fs, $uriStructure->getFileSystem());
        $this->assertEquals($path, $uriStructure->getPath());
        $this->assertEquals($uri, (string)$uriStructure);
    }

    public function getUriList(): array
    {
        return [
            [
                'local',
                'file.txt',
                'local://file.txt',
            ],
            [
                'aws',
                'some/specific/dir/dirFile.txt',
                'aws://some/specific/dir/dirFile.txt',
            ],
        ];
    }

    public function getUriListWithSeparators(): array
    {
        return [
            [
                'local',
                'file.txt',
                'local://file.txt'
            ],
            [
                'local',
                'dir/file.txt',
                'local://dir/file.txt'
            ],
        ];
    }

    public function getBadUriList(): array
    {
        return [
            ['://file.txt', 'Filesystem name can not be empty'],
            ['aws://', 'Filesystem pathname can not be empty'],
        ];
    }
}
