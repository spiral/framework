<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Response;

use Spiral\Components\Http\Message\Stream;
use Spiral\Components\Http\Response;

class FileResponse extends Response
{
    /**
     * FileResponse used to create responses associated with local file stream. Response will automatically
     * create Content-Type (application/octet-stream) and Content-Length headers. Headers can be
     * rewritten manually.
     *
     * @param string $filename   Local filename to be send.
     * @param string $publicName Name show to client.
     * @param int    $statusCode
     * @param array  $headers
     * @param bool   $normalize  Normalize headers case (disabled by default).
     */
    public function __construct(
        $filename,
        $publicName = null,
        $statusCode = 200,
        array $headers = array(),
        $normalize = true
    )
    {
        if (!$publicName)
        {
            $publicName = basename($filename);
        }

        //Forcing default set of headers
        $headers += array(
            'Content-Disposition'       => 'attachment; filename="' . addcslashes($publicName, '"') . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type'              => 'application/octet-stream',
            'Content-Length'            => filesize($filename),
            'Expires'                   => '0',
            'Cache-Control'             => 'no-cache, must-revalidate',
            'Pragma'                    => 'public'
        );

        parent::__construct(new Stream($filename, 'rb'), $statusCode, $headers, $normalize);
    }
}