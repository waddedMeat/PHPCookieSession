<?php

namespace Loco\Request;

const JS_TEMPLATE = <<<EOF
<script>
<!--
document.cookie = "{COOKIE}";
//--> </script>
EOF;

class Cookie implements CookieInterface
{
    public $useJsFallback = false;

    public function get(string $key)
    {
        return $_COOKIE[$key];
    }

    public function set($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        if (headers_sent()) {
            if (!$this->useJsFallback) {
                throw new \RuntimeException("Headers already sent");
            }
            $date = date('r', $expire);
            $string = "$name=$value; expires=$date; domain=$domain; path=$path;";
            foreach (['secure' => $secure, 'HttpOnly' => $httponly] as $flag => $append) {
                $append && $string .= "$flag;";
            }
            $good = print str_replace('{COOKIE}', $string, JS_TEMPLATE);
        } else {
            $good = setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }

        return (bool) $good;
    }
}