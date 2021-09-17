<?php

namespace Loco\Crypt;

/**
 * Interface CipherInterface
 * @package Loco\Crypt
 */
interface CipherInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function encrypt($data): string;

    /**
     * @param mixed $data
     * @return string
     */
    public function decrypt($data): string;
} 
