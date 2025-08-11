<?php

declare(strict_types=1);

namespace Omega\Security;

use Exception;
use Random\RandomException;

use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function hash;
use function openssl_decrypt;
use function openssl_encrypt;
use function random_bytes;

use const OPENSSL_RAW_DATA;

class Crypt
{
    private string $cipherAlgo;

    private string $iv;

    private string $hash;

    /**
     * @throws RandomException
     * @throws Exception
     */
    public function __construct(string $passPhrase, string $cipherAlgo)
    {
        [$this->cipherAlgo, $chars] = $this->algoParse($cipherAlgo);
        $this->iv                   = random_bytes($chars);
        $this->hash                 = $this->hash($passPhrase);
    }

    /**
     * @return string[]|int[]
     * @throws Exception
     */
    private function algoParse(string $cipherAlgo): array
    {
        $parse = explode(';', $cipherAlgo);

        if (count($parse) < 2) {
            throw new Exception('Cipher algo must provide chars length');
        }

        return [$parse[0], (int) $parse[1]];
    }

    public function hash(string $passPhrase): string
    {
        return hash('sha256', $passPhrase, true);
    }

    public function encrypt(string $plainText, ?string $passPhrase = null): string
    {
        $hash = $passPhrase === null ? null : $this->hash($passPhrase);

        return base64_encode(
            openssl_encrypt(
                $plainText,
                $this->cipherAlgo,
                $hash ?? $this->hash,
                OPENSSL_RAW_DATA,
                $this->iv
            )
        );
    }

    public function decrypt(string $encrypted, ?string $passPhrase = null): string
    {
        $hash = $passPhrase === null ? null : $this->hash($passPhrase);

        return openssl_decrypt(
            base64_decode($encrypted),
            $this->cipherAlgo,
            $hash ?? $this->hash,
            OPENSSL_RAW_DATA,
            $this->iv
        );
    }
}
