<?php

namespace Loco\Session\SaveHandler;

use Loco\Crypt\CipherInterface;
use Loco\Crypt\None;

class ClientSession
{

    /**
     * @var CipherInterface
     */
    private $cipher;

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
     * Flag to determine if the headers have already been send
     * @var bool
     */
    private $sent;


    /**
     * Returns the domain used for the cookie or `session.cookie_domain` if it is not set
     * @return string
     */
    public function getDomain()
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
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Returns the lifetime for the cookie or `session.cookie_lifetime` if it does not exist
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime ?: $this->lifetime = (int)ini_get('session.cookie_lifetime');
    }

    /**
     * @param mixed $lifetime
     *
     * @return $this
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Sets the encryption class used to encrypt the session data
     * @param CipherInterface $cypher
     *
     * @return $this
     */
    public function setCipher(CipherInterface $cypher)
    {
        $this->cipher = $cypher;

        return $this;
    }

    /**
     * Returns the encryption class used to encrypt the session data
     * If no cipher is set, a `None` cipher will be returned and will
     * not encrypt anything
     *
     * @return CipherInterface
     */
    public function getCipher()
    {
        return $this->cipher ?: $this->cipher = new None();
    }

    /**
     * Open Session - retrieve resources
     *
     * @param string $savePath
     * @param string $name
     * @return bool
     */
    public function open($savePath, $name)
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
        return $this->getCipher()->decrypt($_COOKIE[$this->name]);
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
        if (!$this->sent) {
            if (headers_sent()) {
                throw new \LogicException('Session data must be written before headers are sent');
            }
            $ttl = $this->getLifetime();
            $expire = $ttl ? $ttl + time() : null;
            $this->sent = (bool)$this->setcookie($this->name, $this->getCipher()->encrypt($data), $expire);
        }

        return $this->sent;
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
        if (!$this->sent) {
            if (headers_sent()) {
                throw new \LogicException('Session data must be destroyed before headers are sent');
            }
            $this->sent = (bool)$this->setCookie($this->name, '', time() - 3600);
        }

        return $this->sent;
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
     * @param string $name
     * @param mixed $value
     * @param int $expire
     * @return bool
     */
    private function setCookie($name, $value, $expire)
    {
        return setcookie($name, $value, $expire, '/', $this->getDomain(), null, true);
    }
}