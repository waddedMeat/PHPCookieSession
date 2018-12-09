<?php

namespace Loco\Request;


interface CookieInterface
{
    public function get(string $key);

    public function set($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false);
}