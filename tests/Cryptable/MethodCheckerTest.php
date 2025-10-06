<?php

namespace Dayploy\DoctrineExtensionsBundle\Tests\Service;

use Dayploy\DoctrineExtensionsBundle\Cryptable\Cryptable;
use Dayploy\DoctrineExtensionsBundle\Cryptable\MethodChecker;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MethodCheckerTest extends KernelTestCase
{
    private MethodChecker $service;

    public function setUp(): void
    {
        $this->service = new MethodChecker();
    }

    #[DoesNotPerformAssertions]
    public function testValidEntity(): void
    {
        $entity = new \Dayploy\DoctrineExtensionsBundle\Tests\Entity\MyClass();
        $this->service->check($entity);
    }

    public function testInvalidEntity(): void
    {
        $entity = new class()
        {
            #[Cryptable(
                nonceProperty: 'myValueNonce',
                encryptedProperty: 'myValueEncrypted',
            )]
            private ?string $myValue = null;
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is missing the method getMyValue');
        $this->service->check($entity);
    }
}
