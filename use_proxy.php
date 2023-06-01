<?php
require '../../vendor/autoload.php';

use KhaiPhan\OpenAi\Auth;

$proxy = '127.0.0.1:3128';

// Proxy authorization
$proxy = array( '127.0.0.1:3128', 'proxy_username', 'proxy_password' );

$OpenAIAuth = new Auth('OpenAI_username', 'OpenAI_password', $proxy);

$auth = $OpenAIAuth->auth();

print_r($auth);
