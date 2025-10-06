<?php

declare(strict_types=1);

namespace Dayploy\DoctrineExtensionsBundle\Cryptable;

interface EncryptionServiceInterface
{
    /**
     * @param string $nonce in binary format
     *
     * @var return string in binary format
     */
    public function encrypt(
        string $nonce,
        #[\SensitiveParameter]
        string $data,
    ): string ;

    /**
     * @param string $nonce         in binary format
     * @param string $encryptedData in binary format
     */
    public function decrypt(
        string $nonce,
        string $encryptedData,
    ): string;

    public function generateNonce(
    ): string;
}
