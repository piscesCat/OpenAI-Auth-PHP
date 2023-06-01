## OpenAIAuth

OpenAIAuth is a Composer package that allows you to authenticate with OpenAI using PHP code.

## Languages

- [Tiếng Việt](README_vi.md)
- [English](README.md)

## Installation

Requires a minimum PHP version of 7.2

Use [Composer](https://getcomposer.org) to install the package.

Run the following command in the terminal:

```
composer require khaiphan/openai-auth:dev-main
```

## Usage

1. First, include the autoloader in your PHP code:

```php
require 'vendor/autoload.php';
```

2. Next, create an instance of the `Auth0` class and provide your OpenAI username and password:

```php
use KhaiPhan\OpenAi\Auth;

$OpenAIAuth = new Auth('OpenAI_username', 'OpenAI_password');
```

3. Then, call the `auth()` method to authenticate and obtain the access token:

```php
$auth = $OpenAIAuth->auth();
```

4. You can access the access token through the `$auth['access_token']` variable. For example:

```php
$accessToken = $auth['access_token'];
echo $accessToken;
```

Make sure to replace `'OpenAI_username'` and `'OpenAI_password'` with your actual authentication credentials.

## License

This package is open source and available under the [MIT License](https://opensource.org/licenses/MIT).
