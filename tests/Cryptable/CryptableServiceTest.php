<?php

namespace Dayploy\DoctrineExtensionsBundle\Tests\Service;

use Dayploy\DoctrineExtensionsBundle\Cryptable\CryptableService;
use Dayploy\DoctrineExtensionsBundle\Cryptable\EncryptionService;
use Dayploy\DoctrineExtensionsBundle\Cryptable\MethodChecker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CryptableServiceTest extends KernelTestCase
{
    private CryptableService $service;

    public function setUp(): void
    {
        $encryptionService = new EncryptionService('0x9df2e1dc84e578fe5937a40dd27739455b5a5817a6d87fe25ea7c5387986d0c0');
        $methodChecker = new MethodChecker();
        $this->service = new CryptableService(
            methodChecker: $methodChecker,
            encryptionService: $encryptionService,
        );
    }

    public function testEncryptValue(): void
    {
        $entity = new \Dayploy\DoctrineExtensionsBundle\Tests\Entity\MyClass();
        $entity->setMyValue('some sensible value');
        $this->service->encryptValue($entity);
        $this->assertNotNull($entity->getMyValueNonce());
        $this->assertNotNull($entity->getMyValueEncrypted());
    }

    public function testEncryptNullValue(): void
    {
        $entity = new \Dayploy\DoctrineExtensionsBundle\Tests\Entity\MyClass();
        $entity->setMyValue(null);
        $this->service->encryptValue($entity);
        $this->assertNull($entity->getMyValueNonce());
        $this->assertNull($entity->getMyValueEncrypted());
    }

    /**
     * The encrypted value is not null, so is the value is decrypted
     */
    public function testDecryptValue(): void
    {
        $entity = new \Dayploy\DoctrineExtensionsBundle\Tests\Entity\MyClass();
        $entity->setMyValueEncrypted(null);
        $entity->setMyValueNonce(base64_decode('WBSUiqe+HE++TCldxQIrfAkOfMNvS9Hzr+D/X5R4TmTXetk='));
        $entity->setMyValueEncrypted(base64_decode('42cflLlWKttFw0tfYuq3IdBnSL1jJU7R'));
        $this->service->decryptValue($entity);
        $this->assertSame('some sensible value', $entity->getMyValue());
    }

    /**
     * The encrypted value is null, so is the value
     */
    public function testDecryptNullValue(): void
    {
        $entity = new \Dayploy\DoctrineExtensionsBundle\Tests\Entity\MyClass();
        $entity->setMyValueEncrypted(null);
        $entity->setMyValue('some value');
        $this->service->decryptValue($entity);
        $this->assertSame(null, $entity->getMyValue());
    }
}
