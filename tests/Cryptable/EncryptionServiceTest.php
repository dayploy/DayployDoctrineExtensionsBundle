<?php

namespace Dayploy\DoctrineExtensionsBundle\Tests\Service;

use Dayploy\DoctrineExtensionsBundle\Cryptable\EncryptionService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EncryptionServiceTest extends KernelTestCase
{
    public function testEncrypt(): void
    {
        $service = new EncryptionService('0x9df2e1dc84e578fe5937a40dd27739455b5a5817a6d87fe25ea7c5387986d0c0');
        $nonce = $service->generateNonce();

        $this->assertSame(24, strlen($nonce));

        $encrypted = $service->encrypt(
            nonce: $nonce,
            data: 'this is a secret',
        );
        $this->assertNotSame('this is a secret', $encrypted);

        $decrypted = $service->decrypt(
            nonce: $nonce,
            encryptedData: $encrypted,
        );
        $this->assertSame('this is a secret', $decrypted);
    }

    public function testDecryptException(): void
    {
        $service = new EncryptionService('0x9df2e1dc84e578fe5937a40dd27739455b5a5817a6d87fe25ea7c5387986d0c0');
        $invalidNonce = $service->generateNonce();

        $this->expectException(\RuntimeException::class);
        $service->decrypt(
            nonce: $invalidNonce,
            encryptedData: 'some invalid data',
        );
    }

    public function testMissingEncryptionKey(): void
    {
        putenv('DOCTRINE_ENCRYPTION_KEY');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The env variable DOCTRINE_ENCRYPTION_KEY must be set to use cryptable doctrine extension');
        new EncryptionService();
    }
}
