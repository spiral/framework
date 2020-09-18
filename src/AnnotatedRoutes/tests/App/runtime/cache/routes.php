<?php return array (
  'index' => 
  array (
    'pattern' => '/',
    'controller' => 'Spiral\\Tests\\Router\\App\\Controller\\HomeController',
    'action' => 'index',
    'group' => 'default',
    'verbs' => 
    array (
      0 => 'GET',
    ),
    'defaults' => 
    array (
    ),
    'middleware' => 
    array (
    ),
  ),
  'method' => 
  array (
    'pattern' => '/',
    'controller' => 'Spiral\\Tests\\Router\\App\\Controller\\HomeController',
    'action' => 'method',
    'group' => 'default',
    'verbs' => 
    array (
      0 => 'POST',
    ),
    'defaults' => 
    array (
    ),
    'middleware' => 
    array (
    ),
  ),
);