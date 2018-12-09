<?php

namespace Loco\Crypt;

/**
 * Interface CipherInterface
 * @package Loco\Crypt
 */
interface CipherInterface
{
    /**
     * @param $data
     * @return string
     */
    public function encrypt($data);

    /**
     * @param $data
     * @return string
     */
    public function decrypt($data);
} 