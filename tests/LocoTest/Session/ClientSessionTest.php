<?php

namespace Loco\Session\SaveHandler;

class ClientSessionTest extends \PHPUnit\Framework\TestCase
{

    public function testDomain()
    {
        $ini = (string)ini_get('session.cookie_domain');

        $obj = new ClientSession();
        $this->assertEquals($ini, $obj->getDomain());

        $domain = ".locosoftworks.com";
        $obj->setDomain($domain);

        $this->assertEquals($domain, $obj->getDomain());
    }


    public function testLifetime()
    {
        $ini = (int)ini_get('session.cookie_lifetime');

        $obj = new ClientSession();
        $this->assertEquals($ini, $obj->getLifetime());

        $lifetime = 1234;
        $obj->setLifetime($lifetime);

        $this->assertEquals($lifetime, $obj->getLifetime());
    }

    public function testCipher()
    {
        $obj = new ClientSession();
        $this->assertInstanceOf(\Loco\Crypt\None::class, $obj->getCipherImpl());

        /** @var \Loco\Crypt\CipherInterface $mock */
        $mock = $this->getMockBuilder(\Loco\Crypt\CipherInterface::class)->getMock();
        $obj->setCipherImpl($mock);

        $this->assertEquals($mock, $obj->getCipherImpl());
    }

    public function testCookieImpl()
    {
        $obj = new ClientSession();
        $this->assertInstanceOf(\Loco\Request\Cookie::class, $obj->getCookieImpl());

        /** @var \Loco\Request\CookieInterface $mock */
        $mock = $this->getMockBuilder(\Loco\Request\CookieInterface::class)->getMock();
        $obj->setCookieImpl($mock);

        $this->assertEquals($mock, $obj->getCookieImpl());
    }

    public function testOpen()
    {
        $obj = new ClientSession();
        $status = $obj->open("path is required by the interface", "session_name");

        $this->assertTrue($status);
    }

    public function testClose()
    {
        $obj = new ClientSession();
        $this->assertTrue($obj->close());
    }

    public function testGc()
    {
        $obj = new ClientSession();
        $this->assertTrue($obj->gc(1234));
    }

    public function testSession()
    {
        $obj = new ClientSession();

        $obj->setLifetime(30);
        $obj->setDomain('.locosoftworks.com');

        $mockCookie = $this->getMockBuilder(\Loco\Request\CookieInterface::class)
            ->setMethods(['get', 'set'])
            ->getMock();

        $mockCookie->method('get')
            ->with($this->equalTo('session_name'))
            ->willReturn('session_data');

        $mockCookie->method('set')
            ->with(
                $this->equalTo('session_name'),
                $this->equalTo('session_data'),
                $this->equalTo(30 + time())
            )
            ->willReturn(true);

        /**
         * @var $mockCookie \Loco\Request\CookieInterface
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

    public function testDestroy()
    {
        $obj = new ClientSession();

        $obj->setLifetime(30);
        $obj->setDomain('.locosoftworks.com');

        $mockCookie = $this->getMockBuilder(\Loco\Request\CookieInterface::class)
            ->getMock();

        $mockCookie->method('set')
            ->with(
                $this->equalTo('session_name'),
                $this->equalTo(''),
                $this->lessThan(time())
            )
            ->willReturn(true);

        $obj->setCookieImpl($mockCookie);

        $obj->open('this does not matter', 'session_name');

        $status = $obj->destroy('this does not matter');
        $this->assertTrue($status);
    }
}

// override the time() function to return static data for testing
function time()
{
    return 1492;
}
