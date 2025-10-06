<?php

namespace Dayploy\DoctrineExtensionsBundle\Tests\Service;

use Dayploy\DoctrineExtensionsBundle\DayployDoctrineExtensionsBundle;
use Dayploy\DoctrineExtensionsBundle\Tests\Entity\MyClass;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Nyholm\BundleTest\TestKernel;
use Symfony\Component\HttpKernel\KernelInterface;

class CryptableListenerTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(DoctrineBundle::class);
        $kernel->addTestBundle(DayployDoctrineExtensionsBundle::class);
        $kernel->handleOptions($options);
        $kernel->addTestConfig(__DIR__ . '/../config.yml');

        putenv('DOCTRINE_ENCRYPTION_KEY=0x9df2e1dc84e578fe5937a40dd27739455b5a5817a6d87fe25ea7c5387986d0c0');

        return $kernel;
    }

    public function testCreateAndUpdate(): void
    {
        $kernel = self::bootKernel();
        $container = self::getContainer();

        $this->initDatabase($kernel);

        $em = $container->get(EntityManagerInterface::class);

        // persist with value
        $entity = new MyClass();
        $entity->setId(1);
        $entity->setMyValue('some sensitive value');

        $em->persist($entity);
        $em->flush();
        $this->assertNotNull($entity->getMyValueNonce());
        $this->assertNotNull($entity->getMyValueEncrypted());

        // update with null
        $entity->setMyValue(null);
        $em->flush();
        $this->assertNull($entity->getMyValueNonce());
        $this->assertNull($entity->getMyValueEncrypted());

        // persist without value
        $entity = new MyClass();
        $entity->setId(2);
        $entity->setMyValue(null);

        $em->persist($entity);
        $em->flush();
        $this->assertNull($entity->getMyValueNonce());
        $this->assertNull($entity->getMyValueEncrypted());

        // update with value
        $entity->setMyValue('some sensitive value');
        $em->flush();
        $this->assertNotNull($entity->getMyValueNonce());
        $this->assertNotNull($entity->getMyValueEncrypted());

        // update with same value
        // the listener did not change data
        $previousNonce = $entity->getMyValueNonce();
        $previousEncrypted = $entity->getMyValueEncrypted();
        $entity->setMyValue('some sensitive value');
        $em->flush();
        $this->assertSame($previousNonce, $entity->getMyValueNonce());
        $this->assertSame($previousEncrypted, $entity->getMyValueEncrypted());

        // update with other value
        // the listener changed data
        $entity->setMyValue('some other sensitive value');
        $em->flush();
        $this->assertNotSame($previousNonce, $entity->getMyValueNonce());
        $this->assertNotSame($previousEncrypted, $entity->getMyValueEncrypted());
    }

    public function testLoad(): void
    {
        $kernel = self::bootKernel();
        $container = self::getContainer();

        $this->initDatabase($kernel);

        $em = $container->get(EntityManagerInterface::class);

        // persist with value
        $entity = new MyClass();
        $entity->setId(1);
        $entity->setMyValue('some sensitive value');

        $em->persist($entity);
        $em->flush();

        // purge UnitOfWork
        $em->clear();
        $repository = $em->getRepository(MyClass::class);
        $entities = $repository->findAll();
        $this->assertCount(1, $entities);

        /** @var MyClass */
        $firstEntity = $entities[0];
        $this->assertSame('some sensitive value', $firstEntity->getMyValue());
    }

    private function initDatabase(
        KernelInterface $kernel,
    ): void {
        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }
}
