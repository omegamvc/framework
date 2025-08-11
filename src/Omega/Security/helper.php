<?php

declare(strict_types=1);

namespace Omega\Security;

use Random\RandomException;

if (!function_exists('encrypt')) {
    /**
     * Encrypt.
     *
     * @param string      $plain_text
     * @param string|null $pass_phrase
     * @param string      $algo
     * @return string
     * @throws RandomException
     */
    function encrypt(string $plain_text, ?string $pass_phrase = null, string $algo = Algo::AES_256_CBC): string
    {
        return (new Crypt($pass_phrase, $algo))->encrypt($plain_text);
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt.
     *
     * @param string      $encrypted
     * @param string|null $pass_phrase
     * @param string      $algo
     * @return string
     * @throws RandomException
     */
    function decrypt(string $encrypted, ?string $pass_phrase = null, string $algo = Algo::AES_256_CBC): string
    {
        return (new Crypt($pass_phrase, $algo))->decrypt($encrypted);
    }
}
