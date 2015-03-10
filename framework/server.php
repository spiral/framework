<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

$requestURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$out = fopen('php://stdout', 'w');

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $logMessage = '[' . date('M d, Y H:i:s') . '] GET ';
}
else
{
    $logMessage = '[' . date('M d, Y H:i:s') . '] <fg=cyan>' . $_SERVER['REQUEST_METHOD'] . '</fg=cyan>';
}

if ($requestURI !== '/' && file_exists(getcwd() . $requestURI))
{
    //CLI-Server will handle resources by itself.
    fwrite($out, 'R ' . $logMessage . ' ' . $requestURI);

    return false;
}

fwrite($out, 'S ' . $logMessage . ' <info>' . $_SERVER['REQUEST_URI'] . '</info>');
require_once 'index.php';