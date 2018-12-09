<?php

namespace Loco\Session\SaveHandler;

use Loco\Crypt\CipherInterface;
use Loco\Crypt\None;
use Loco\Request\Cookie;
use Loco\Request\CookieInterface;
use SessionHandlerInterface;

class ClientSession implements SessionHandlerInterface
{

    /**
     * @var CipherInterface
     */
    private $cipherImpl;

    /**
     * @var CookieInterface
     */
    private $cookieImpl;
    /**
     * The domain used for setting the cookie
     *
     * @var string
     */
    private $domain;

    /**
     * The name of the session
     * @var string
     */
    private $name;

    /**
     * Time used for expiring the token
     * @see session.cookie_lifetime
     * @var integer
     */
    private $lifetime;

    /**
     * Open Session - retrieve resources
     *
     * @param string $savePath
     * @param string $name
     * @return bool
     */
    public function open($savePath, $name): bool
    {
        $this->name = $name;

        return true;
    }

    /**
     * Close Session - free resources
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        return $this->getCipherImpl()->decrypt($this->getCookieImpl()->get($this->name));
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     * @return bool
     * @throws \LogicException
     */
    public function write($id, $data)
    {
        $ttl = $this->getLifetime();
        $expire = $ttl ? $ttl + time() : null;
        return $this->getCookieImpl()->set($this->name, $this->getCipherImpl()->encrypt($data), $expire);
    }

    /**
     * Returns the lifetime for the cookie or `session.cookie_lifetime` if it does not exist
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime ?: $this->lifetime = (int)ini_get('session.cookie_lifetime');
    }

    /**
     * @param mixed $lifetime
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
        return $this->domain ?: $this->domain = (string)ini_get('session.cookie_domain');
    }

    /**
     * Sets the domain used for setting the cookie
     *
     * @param $domain
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
     * @throws \LogicException
     */
    public function destroy($id)
    {
        return $this->getCookieImpl()->set($this->name, '', time() - 3600);
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
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
        return $this->cipherImpl ?: $this->cipherImpl = new None();
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

}