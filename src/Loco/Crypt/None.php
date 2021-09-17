<?php

namespace Loco\Crypt;

/**
 * Class None
 * @package Loco\Crypt
 */
class None implements CipherInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function encrypt($data): string
    {
        return $data;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function decrypt($data): string
    {
        return $data;
    }
}
