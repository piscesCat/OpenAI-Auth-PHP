<?php
require '../../vendor/autoload.php';

use KhaiPhan\OpenAi\Auth;

$OpenAIAuth = new Auth('OpenAI_username', 'OpenAI_password');

$auth = $OpenAIAuth->auth();

$accessToken = $auth['access_token'];

echo $accessToken;
