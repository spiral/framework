<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Traits;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides ability to write json into responses.
 */
trait JsonTrait
{
    /**
     * Generate JSON response.
     *
     * @param ResponseInterface $response
     * @param mixed             $json
     * @param int               $code
     *
     * @return ResponseInterface
     */
    private function writeJson(
        ResponseInterface $response,
        $json,
        int $code = 200
    ): ResponseInterface {
        if ($json instanceof \JsonSerializable) {
            $json = $json->jsonSerialize();
        }
        
        if (is_array($json) && isset($json['status'])) {
            $code = $json['status'];
        }

        $response->getBody()->write(json_encode($json));

        return $response->withStatus($code)->withHeader('Content-Type', 'application/json');
    }
}
