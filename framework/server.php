<?php
/**
 * CLI-Server will handle resources by itself.
 */
$requestURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$out = fopen('php://stdout', 'w');

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $logMessage = '[' . date('M d, Y H:i:s') . '] GET ';
}
else
{
    $logMessage = '[' . date('M d, Y H:i:s') . '] <fg=comment>' . $_SERVER['REQUEST_METHOD'] . '</fg=comment>';
}

if ($requestURI !== '/' && file_exists(getcwd() . $requestURI))
{
    //Letting process know about resource
    fwrite($out, 'R ' . $logMessage . ' ' . $requestURI);

    //Serving resources
    return false;
}

fwrite($out, 'S ' . $logMessage . ' <info>' . $_SERVER['REQUEST_URI'] . '</info>');

require_once 'index.php';