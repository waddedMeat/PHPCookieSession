<?php

namespace Loco\Request;


class Cookie implements CookieInterface
{
    public function get(string $key)
    {
        return $_COOKIE[$key];
    }

    public function set($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        if (headers_sent()) {
            throw new \LogicException('Headers already sent');
        }
        if ($status = setcookie($name, $value, $expire, $path, $domain, $secure, $httponly)) {
            $_COOKIE[$name] = $value;
        }
        return $status;
    }
}