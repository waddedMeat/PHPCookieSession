<?php

namespace Loco\Crypt;

/**
 * Class None
 * @package Loco\Crypt
 */
class None implements CipherInterface
{
    /**
     * @param $data
     * @return mixed
     */
    public function encrypt($data)
    {
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function decrypt($data)
    {
        return $data;
    }
}
 