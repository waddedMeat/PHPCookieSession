<?php

namespace Loco\Http;

use RuntimeException;

const JS_TEMPLATE = <<<EOF
<script>
document.cookie = "{COOKIE}";
</script>
EOF;

class Cookie implements CookieInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @param array<string, mixed>|null $data
     */
    public function __construct(array $data = null)
    {
        $this->data = $data ?? $_COOKIE;
    }

    /**
     * @var bool
     */
    public bool $useJsFallback = false;

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param false $secure
     * @param false $httponly
     * @return bool
     */
    public function set(
        $name,
        $value = "",
        $expire = 0,
        $path = "",
        $domain = "",
        $secure = false,
        $httponly = false
    ): bool {
        if (headers_sent()) {
            if (!$this->useJsFallback) {
                throw new RuntimeException("Headers already sent");
            }
            $date = date('r', $expire);
            $string = "$name=$value; expires=$date; domain=$domain; path=$path;";
            foreach (['secure' => $secure, 'HttpOnly' => $httponly] as $flag => $append) {
                if (!empty($append)) {
                    $string .= "$flag;";
                }
            }
            $good = print str_replace('{COOKIE}', $string, JS_TEMPLATE);
        } else {
            $good = $this->setCookie($name, $value, $expire, $path, $domain);
        }

        return (bool)$good;
    }

    protected function setCookie(string $name, string $value, int $expire, string $path, string $domain): bool
    {
        return setcookie($name, $value, $expire, $path, $domain, true, true);
    }
}
