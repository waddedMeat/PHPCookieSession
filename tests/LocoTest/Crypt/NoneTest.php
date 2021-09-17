<?php

use Loco\Crypt\None;
use PHPUnit\Framework\TestCase;

class NoneTest extends TestCase
{

    public function testEncrypt()
    {
        $crypt = new None();
        $this->assertEquals('nothing', $crypt->encrypt('nothing'));
    }

    public function testDecrypt()
    {
        $crypt = new None();
        $this->assertEquals('nothing', $crypt->decrypt('nothing'));
    }
}
