<?php
/**
 * OpenAIAuth
 *
 * Author: Khải Phan
 *
 * Description:
 * A PHP library for authenticating with OpenAI. This library provides a comprehensive set of methods and functions
 * to build an authentication workflow with the OpenAI API in your PHP applications. By utilizing this library,
 * developers can seamlessly handle the authentication process, including obtaining the Access Token from OpenAI.
 *
 * Please refer to the extensive documentation for detailed instructions on how to effectively use this library
 * and explore concrete examples to kickstart your work with OpenAI in your PHP applications.
 */

namespace KhaiPhan\OpenAi;

use Exception;

class Auth
{
    private $email;
    private $password;
    private $req_options;
    private $user_agent;

    /**
     * Auth0 constructor.
     * @param string $email User's email
     * @param string $password User's password
     * @param string|array|null $proxy Proxy server (optional)
     */
    public function __construct($email, $password, $proxy = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->user_agent =
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36";
        $this->req_options = [
            "timeout" => 100,
            "redirects" => 10,
            "verify" => true,
        ];
        if (isset($proxy)) {
            $this->req_options["proxy"] = $proxy;
        }
    }

    /**
     * Checks if the email is valid.
     * @param string $email User's email
     * @return bool Returns true if the email is valid, false otherwise
     */
    private function checkEmail($email)
    {
        $regex = "/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,7}\b/";
        return preg_match($regex, $email);
    }

    /**
     * Performs the authentication process.
     * @return mixed Returns the access token if authentication is successful
     * @throws Exception Throws an exception if the email or password is invalid
     */
    public function auth()
    {
        if (!$this->checkEmail($this->email) || !$this->password) {
            throw new Exception("Invalid email or password.");
        }

        return $this->partTwo();
    }

    /**
     * Executes part two of the authentication process.
     * @return mixed Returns the result of part three
     */
    private function partTwo()
    {
        $code_challenge = "w6n3Ix420Xhhu-Q5-mOOEyuPZmAsJHUbBpO8Ub7xBCY";
        $code_verifier = "yGrXROHx_VazA0uovsxKfE263LMFcrSrdm4SlC-rob8";
        $url =
            "https://auth0.openai.com/authorize?client_id=pdlLIX2Y72MIl2rhLhTE9VV9bN905kBh&audience=https%3A%2F%2Fapi.openai.com%2Fv1&redirect_uri=com.openai.chat%3A%2F%2Fauth0.openai.com%2Fios%2Fcom.openai.chat%2Fcallback&scope=openid%20email%20profile%20offline_access%20model.request%20model.read%20organization.read%20offline&response_type=code&code_challenge=" .
            $code_challenge .
            "&code_challenge_method=S256&prompt=login";

        return $this->partThree($code_verifier, $url);
    }

    /**
     * Executes part three of the authentication process.
     * @param string $code_verifier Code verifier
     * @param string $url URL for the request
     * @return mixed Returns the result of part four
     * @throws Exception Throws an exception if there is an error in the request
     */
    private function partThree($code_verifier, $url)
    {
        $headers = [
            "User-Agent" => $this->user_agent,
            "Referer" => "https://ios.chat.openai.com/",
        ];
        $resp = \WpOrg\Requests\Requests::get(
            $url,
            $headers,
            $this->req_options
        );
        $httpCode = $resp->status_code;
        if ($httpCode === 200) {
            preg_match("/\?(.*)/", $resp->url, $matches);
            $url_params = [];
            parse_str($matches[1], $url_params);
            $state = $url_params["state"];
            return $this->partFour($code_verifier, $state, $resp->cookies);
        } else {
            throw new Exception("Error request login url.");
        }
    }

    /**
     * Executes part four of the authentication process.
     * @param string $code_verifier Code verifier
     * @param string $state State parameter
     * @param string|array $cookies Cookies (string or array format)
     * @return mixed Returns the result of part five
     * @throws Exception Throws an exception if there is an error in the request
     */
    private function partFour($code_verifier, $state, $cookies)
    {
        $url = "https://auth0.openai.com/u/login/identifier?state=" . $state;
        $headers = [
            "User-Agent" => $this->user_agent,
            "Referer" => $url,
            "Origin" => "https://auth0.openai.com",
        ];
        $data = [
            "state" => $state,
            "username" => $this->email,
            "js-available" => "true",
            "webauthn-available" => "true",
            "is-brave" => "false",
            "webauthn-platform-available" => "false",
            "action" => "default",
        ];
        $this->req_options["cookies"] = $cookies;
        $this->req_options["follow_redirects"] = false;
        $resp = \WpOrg\Requests\Requests::post(
            $url,
            $headers,
            $data,
            $this->req_options
        );
        $httpCode = $resp->status_code;
        if ($httpCode === 302) {
            return $this->partFive($code_verifier, $state);
        } else {
            throw new Exception("Error check email.");
        }
    }

    /**
     * Executes part five of the authentication process.
     * @param string $code_verifier Code verifier
     * @param string $state State parameter
     * @return mixed Returns the result of part six
     * @throws Exception Throws an exception if there is an error in the request
     */
    private function partFive($code_verifier, $state)
    {
        $url = "https://auth0.openai.com/u/login/password?state=" . $state;
        $headers = [
            "User-Agent: " . $this->user_agent,
            "Referer: " . $url,
            "Origin: https://auth0.openai.com",
        ];
        $data = [
            "state" => $state,
            "username" => $this->email,
            "password" => $this->password,
            "action" => "default",
        ];
        $this->req_options["follow_redirects"] = false;
        $resp = \WpOrg\Requests\Requests::post(
            $url,
            $headers,
            $data,
            $this->req_options
        );
        $httpCode = $resp->status_code;
        if ($httpCode === 302) {
            $location = $resp->headers["location"];
            if (!preg_match("/\/authorize\/resume\?/", $location)) {
                throw new Exception("Login failed.");
            }
            preg_match("/\?(.*)/", $resp->headers["location"], $matches);
            $url_params = [];
            parse_str($matches[1], $url_params);
            $state = $url_params["state"];
            return $this->partSix($code_verifier, $state);
        } elseif ($httpCode === 400) {
            throw new Exception("Wrong email or password.");
        } else {
            throw new Exception("Error login.");
        }
    }

    /**
     * Executes part six of the authentication process.
     * @param string $code_verifier Code verifier
     * @param string $state State parameter
     * @return mixed Returns the result of getting the access token
     * @throws Exception Throws an exception if there is an error in the request
     */
    private function partSix($code_verifier, $state)
    {
        $url = "https://auth0.openai.com/authorize/resume?state=" . $state;
        $headers = [
            "User-Agent: " . $this->user_agent,
            "Referer: " . $url,
            "Origin: https://auth0.openai.com",
        ];
        $this->req_options["follow_redirects"] = false;
        $resp = \WpOrg\Requests\Requests::get(
            $url,
            $headers,
            $this->req_options
        );
        $httpCode = $resp->status_code;
        if ($httpCode === 302) {
            $location = $resp->headers["location"];
            if (
                !preg_match(
                    "/com\.openai\.chat\:\/\/auth0\.openai\.com\/ios\/com\.openai\.chat\/callback\?/",
                    $location
                )
            ) {
                throw new Exception("Login callback failed.");
            }

            return $this->getAccessToken($code_verifier, $location);
        } elseif ($httpCode === 400) {
            throw new Exception("Wrong email or password.");
        } else {
            throw new Exception("Error login.");
        }
    }

    /**
     * Gets the access token.
     * @param string $code_verifier Code verifier
     * @param string $callback_url Callback URL
     * @return mixed Returns the access token
     * @throws Exception Throws an exception if there is an error in the request
     */
    private function getAccessToken($code_verifier, $callback_url)
    {
        preg_match("/\?(.*)/", $callback_url, $matches);
        $url_params = [];
        parse_str($matches[1], $url_params);
        if (isset($url_params["error"])) {
            $error = $url_params["error"];
            $error_description = isset($url_params["error_description"])
                ? $url_params["error_description"]
                : "";
            throw new Exception("$error: $error_description");
        }

        if (!isset($url_params["code"])) {
            throw new Exception("Error get code from callback url.");
        }
        $url = "https://auth0.openai.com/oauth/token";
        $headers = ["User-Agent: " . $this->user_agent];
        $data = [
            "redirect_uri" =>
                "com.openai.chat://auth0.openai.com/ios/com.openai.chat/callback",
            "grant_type" => "authorization_code",
            "client_id" => "pdlLIX2Y72MIl2rhLhTE9VV9bN905kBh",
            "code" => $url_params["code"],
            "code_verifier" => $code_verifier,
        ];
        $resp = \WpOrg\Requests\Requests::post(
            $url,
            $headers,
            $data,
            $this->req_options
        );
        $httpCode = $resp->status_code;
        if ($httpCode === 200) {
            $json = json_decode($resp->body, true);
            if (!isset($json["access_token"])) {
                throw new Exception(
                    "Get access token failed, maybe you need a proxy."
                );
            }

            return $json;
        } else {
            throw new Exception("Error getting access token: " . $resp->body);
        }
    }
}
