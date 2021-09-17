<?php

namespace Loco\Http;


interface CookieInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param false $secure
     * @param false $httponly
     * @return mixed
     */
    public function set($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false): mixed;
}
