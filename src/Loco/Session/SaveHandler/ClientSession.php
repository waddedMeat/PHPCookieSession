<?php

namespace Loco\Session\SaveHandler;

use Loco\Crypt\CipherInterface;
use Loco\Crypt\None;
use Loco\Http\Cookie;
use Loco\Http\CookieInterface;
use LogicException;
use SessionHandlerInterface;

class ClientSession implements SessionHandlerInterface
{

    /**
     * @var CipherInterface
     */
    private CipherInterface $cipherImpl;

    /**
     * @var CookieInterface
     */
    private CookieInterface $cookieImpl;

    /**
     * @var string
     */
    private string $domain;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var int
     */
    private int $lifetime;

    /**
     * @param string $path
     * @param string $name
     * @return bool
     */
    public function open($path, $name): bool
    {
        $this->name = $name;

        return true;
    }

    /**
     * Close Session - free resources
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id): string
    {
        return $this->getCipherImpl()->decrypt($this->getCookieImpl()->get($this->name));
    }

    /**
     * Returns the encryption class used to encrypt the session data
     * If no cipher is set, a `None` cipher will be returned and will
     * not encrypt anything
     *
     * @return CipherInterface
     */
    public function getCipherImpl(): CipherInterface
    {
        return $this->cipherImpl ?? $this->cipherImpl = new None();
    }

    /**
     * Sets the encryption class used to encrypt the session data
     * @param CipherInterface $cypher
     *
     * @return $this
     */
    public function setCipherImpl(CipherInterface $cypher): SessionHandlerInterface
    {
        $this->cipherImpl = $cypher;

        return $this;
    }

    /**
     * @return CookieInterface
     */
    public function getCookieImpl(): CookieInterface
    {
        return $this->cookieImpl ?? $this->cookieImpl = new Cookie();
    }

    /**
     * @param CookieInterface $cookieImpl
     * @return SessionHandlerInterface
     */
    public function setCookieImpl(CookieInterface $cookieImpl): SessionHandlerInterface
    {
        $this->cookieImpl = $cookieImpl;
        return $this;
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     * @return bool
     * @throws LogicException
     */
    public function write($id, $data): bool
    {
        $ttl = $this->getLifetime();
        $expire = $ttl ? $ttl + time() : 0;
        return $this->getCookieImpl()->set($this->name, $this->getCipherImpl()->encrypt($data), $expire);
    }

    /**
     * Returns the lifetime for the cookie or `session.cookie_lifetime` if it does not exist
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime ?? $this->lifetime = (int)ini_get('session.cookie_lifetime');
    }

    /**
     * @param int $lifetime
     *
     * @return $this
     */
    public function setLifetime(int $lifetime): SessionHandlerInterface
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Returns the domain used for the cookie or `session.cookie_domain` if it is not set
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain ?? $this->domain = (string)ini_get('session.cookie_domain');
    }

    /**
     * Sets the domain used for setting the cookie
     *
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain(string $domain): SessionHandlerInterface
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     * @return bool
     * @throws LogicException
     */
    public function destroy($id): bool
    {
        return $this->getCookieImpl()->set($this->name, '', time() - 3600);
    }

    /**
     * Garbage Collection - remove old session data older
     * than $max_lifetime (in seconds)
     *
     * @param int $max_lifetime
     * @return bool
     */
    public function gc($max_lifetime): bool
    {
        return true;
    }
}
