<?php

declare(strict_types=1);

namespace Dayploy\DoctrineExtensionsBundle\Cryptable;

class EncryptionService implements EncryptionServiceInterface
{
    private string $encryptionKey;

    public function __construct(
        #[\SensitiveParameter]
        ?string $encryptionKeyAsString = null,
    ) {
        if ($encryptionKeyAsString === null) {
            $envValue = getenv('DOCTRINE_ENCRYPTION_KEY');
            if (false === $envValue) {
                throw new \Exception('The env variable DOCTRINE_ENCRYPTION_KEY must be set to use cryptable doctrine extension');
            }

            $encryptionKeyAsString = $envValue;
        }

        $hexString = ltrim($encryptionKeyAsString, '0x');
        $bytes = hex2bin($hexString);

        $this->encryptionKey = $bytes;
    }

    /**
     * @param string $nonce in binary format
     *
     * @var return string in binary format
     */
    public function encrypt(
        string $nonce,
        #[\SensitiveParameter]
        string $data,
    ): string {
        return sodium_crypto_secretbox($data, $nonce, $this->encryptionKey);
    }

    /**
     * @param string $nonce         in binary format
     * @param string $encryptedData in binary format
     */
    public function decrypt(
        string $nonce,
        string $encryptedData,
    ): string {
        $decrypted = sodium_crypto_secretbox_open($encryptedData, $nonce, $this->encryptionKey);

        if (false === $decrypted) {
            throw new \RuntimeException('Déchiffrement échoué : clé ou nonce incorrect.');
        }

        return $decrypted;
    }

    public function generateNonce(
    ): string {
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    }
}
