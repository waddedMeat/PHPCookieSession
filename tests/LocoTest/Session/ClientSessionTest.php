<?php

namespace Loco\Session\SaveHandler;

use Loco\Crypt\CipherInterface;
use Loco\Crypt\None;
use Loco\Http\Cookie;
use Loco\Http\CookieInterface;
use PHPUnit\Framework\TestCase;

class ClientSessionTest extends TestCase
{

    public function testDomain(): void
    {
        $ini = (string)ini_get('session.cookie_domain');

        $obj = new ClientSession();
        $this->assertEquals($ini, $obj->getDomain());

        $domain = ".locosoftworks.com";
        $obj->setDomain($domain);

        $this->assertEquals($domain, $obj->getDomain());
    }


    public function testLifetime(): void
    {
        $ini = (int)ini_get('session.cookie_lifetime');

        $obj = new ClientSession();
        $this->assertEquals($ini, $obj->getLifetime());

        $lifetime = 1234;
        $obj->setLifetime($lifetime);

        $this->assertEquals($lifetime, $obj->getLifetime());
    }

    public function testCipher(): void
    {
        $obj = new ClientSession();
        $this->assertInstanceOf(None::class, $obj->getCipherImpl());

        /** @var CipherInterface $mock */
        $mock = $this->getMockBuilder(CipherInterface::class)->getMock();
        $obj->setCipherImpl($mock);

        $this->assertEquals($mock, $obj->getCipherImpl());
    }

    public function testCookieImpl(): void
    {
        $obj = new ClientSession();
        $this->assertInstanceOf(Cookie::class, $obj->getCookieImpl());

        /** @var CookieInterface $mock */
        $mock = $this->getMockBuilder(CookieInterface::class)->getMock();
        $obj->setCookieImpl($mock);

        $this->assertEquals($mock, $obj->getCookieImpl());
    }

    public function testOpen(): void
    {
        $obj = new ClientSession();
        $status = $obj->open("path is required by the interface", "session_name");

        $this->assertTrue($status);
    }

    public function testClose(): void
    {
        $obj = new ClientSession();
        $this->assertTrue($obj->close());
    }

    public function testGc(): void
    {
        $obj = new ClientSession();
        $this->assertTrue($obj->gc(1234));
    }

    public function testSession(): void
    {
        $obj = new ClientSession();

        $obj->setLifetime(30);
        $obj->setDomain('.locosoftworks.com');

        $mockCookie = new class implements CookieInterface {
            public function get(string $key)
            {
                return $key === 'session_name' ? 'session_data' : null;
            }

            public function set(
                $name,
                $value = "",
                $expire = 0,
                $path = "",
                $domain = "",
                $secure = false,
                $httponly = false
            ) {
                return (
                    $name === 'session_name' &&
                    $value === 'session_data' &&
                    $expire === time() + 30
                );
            }
        };

        /**
         * @var $mockCookie CookieInterface
         */
        $obj->setCookieImpl($mockCookie);

        $status = $obj->open('this does not matter', 'session_name');
        $this->assertTrue($status);

        $data = $obj->read('this does not matter');
        $this->assertEquals('session_data', $data);

        $status = $obj->write('this does not matter', 'session_data');
        $this->assertTrue($status);

        $status = $obj->close();
        $this->assertTrue($status);
    }

    public function testDestroy(): void
    {
        $obj = new ClientSession();

        $obj->setLifetime(30);
        $obj->setDomain('.locosoftworks.com');

        $mockCookie = new class implements CookieInterface {

            public function get(string $key)
            {
                // TODO: Implement get() method.
            }

            public function set(
                $name,
                $value = "",
                $expire = 0,
                $path = "",
                $domain = "",
                $secure = false,
                $httponly = false
            ): bool {
                return (
                    $name === 'session_name' &&
                    $value === '' &&
                    $expire < time()
                );
            }
        };

        $obj->setCookieImpl($mockCookie);

        $obj->open('this does not matter', 'session_name');

        $status = $obj->destroy('this does not matter');
        $this->assertTrue($status);
    }
}

// override the time() function to return static data for testing
function time(): int
{
    return 1492;
}
