## OpenAIAuth

OpenAIAuth là một package Composer cho phép đăng nhập vào OpenAI bằng mã PHP.

## Ngôn ngữ

- [English](README.md)
- [Tiếng Việt](README_vi.md)

## Cài đặt

Yêu cầu phiên bản PHP tối thiểu là 7.2

Sử dụng [Composer](https://getcomposer.org) để cài đặt gói.

Chạy lệnh sau trong terminal:

```
composer require khaiphan/openai-auth:dev-main
```

## Sử dụng

1. Đầu tiên, bạn cần include autoloader trong mã PHP của bạn:

```php
require 'vendor/autoload.php';
```

2. Tiếp theo, tạo một instance của lớp `Auth0` và cung cấp tên người dùng OpenAI và mật khẩu:

```php
use KhaiPhan\OpenAi\Auth;

$OpenAIAuth = new Auth('OpenAI_username', 'OpenAI_password');
```

3. Sau đó, gọi phương thức `auth()` để xác thực và lấy mã truy cập (access token):

```php
$auth = $OpenAIAuth->auth();
```

4. Bạn có thể truy cập mã truy cập (access token) thông qua biến `$auth['access_token']`. Ví dụ:

```php
$accessToken = $auth['access_token'];
echo $accessToken;
```

Đảm bảo rằng bạn đã thay thế `'OpenAI_username'` và `'OpenAI_password'` bằng thông tin xác thực thực tế của bạn.

## License

Gói này là mã nguồn mở và có sẵn theo [MIT License](https://opensource.org/licenses/MIT).
