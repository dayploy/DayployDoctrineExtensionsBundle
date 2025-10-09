<?php

declare(strict_types=1);

namespace Dayploy\DoctrineExtensionsBundle\Cryptable;

use ReflectionAttribute;

class CryptableService
{
    public function __construct(
        private MethodChecker $methodChecker,
        private EncryptionServiceInterface $encryptionService,
    ) {}

    public function encryptValue(
        object $entity,
    ): void {
        $reflector = new \ReflectionClass($entity::class);

        do {
            foreach ($reflector->getProperties() as $property) {
                $attributes = $property->getAttributes(Cryptable::class);

                /** @var ReflectionAttribute $attribute */
                foreach ($attributes as $attribute) {
                    $propertyName = $property->getName();
                    $arguments = $attribute->getArguments();

                    $encryptedProperty = $arguments['nonceProperty'];
                    $nonceProperty = $arguments['encryptedProperty'];

                    $this->encryptEntity(
                        entity: $entity,
                        propertyName: $propertyName,
                        encryptedProperty: $encryptedProperty,
                        nonceProperty: $nonceProperty,
                    );
                }
            }
        } while ($reflector = $reflector->getParentClass());
    }

    public function decryptValue(
        object $entity,
    ): void {
        $reflector = new \ReflectionClass($entity::class);

        do {
            foreach ($reflector->getProperties() as $property) {
                $attributes = $property->getAttributes(Cryptable::class);

                /** @var ReflectionAttribute $attribute */
                foreach ($attributes as $attribute) {
                    $propertyName = $property->getName();
                    $arguments = $attribute->getArguments();

                    $encryptedProperty = $arguments['nonceProperty'];
                    $nonceProperty = $arguments['encryptedProperty'];

                    $this->decryptEntity(
                        entity: $entity,
                        propertyName: $propertyName,
                        encryptedProperty: $encryptedProperty,
                        nonceProperty: $nonceProperty,
                    );
                }
            }
        } while ($reflector = $reflector->getParentClass());
    }

    private function shouldUpdateEntity(
        object $entity,
        string $propertyName,
        #[\SensitiveParameter]
        ?string $currentValue,
    ): bool {
        $previousValue = $this->getPreviousValue(
            entity: $entity,
            propertyName: $propertyName,
        );

        return $currentValue !== $previousValue;
    }

    private function encryptEntity(
        object $entity,
        string $propertyName,
        string $encryptedProperty,
        string $nonceProperty,
    ): void {
        $this->methodChecker->check($entity);

        $rawDataGetter = 'get' . ucwords($propertyName);
        $rawData = $entity->$rawDataGetter();

        if (false === $this->shouldUpdateEntity(
            entity: $entity,
            propertyName: $propertyName,
            currentValue: $rawData,
        )) {
            // the value did not change, do nothing
            return;
        }

        $nonceSetter = 'set' . ucwords($nonceProperty);
        $encryptedPropertySetter = 'set' . ucwords($encryptedProperty);

        $this->setPreviousValue(
            entity: $entity,
            propertyName: $propertyName,
            value: $rawData,
        );

        if (null === $rawData) {
            $entity->$nonceSetter(null);
            $entity->$encryptedPropertySetter(null);
            return;
        }

        $nonceValue = $this->encryptionService->generateNonce();
        $encryptedValue = $this->encryptionService->encrypt(
            nonce: $nonceValue,
            data: $rawData,
        );

        $entity->$nonceSetter($nonceValue);
        $entity->$encryptedPropertySetter($encryptedValue);
    }

    private function decryptEntity(
        object $entity,
        string $propertyName,
        string $encryptedProperty,
        string $nonceProperty,
    ): void {
        $this->methodChecker->check($entity);

        $nonceGetter = 'get' . ucwords($nonceProperty);
        $encryptedPropertyGetter = 'get' . ucwords($encryptedProperty);

        $nonceValue = $entity->$nonceGetter();
        $encryptedValue = $entity->$encryptedPropertyGetter();

        $rawDataSetter = 'set' . ucwords($propertyName);
        if (null === $encryptedValue) {
            $entity->$rawDataSetter(null);

            $this->setPreviousValue(
                entity: $entity,
                propertyName: $propertyName,
                value: null,
            );
            return;
        }

        $decryptedValue = $this->encryptionService->decrypt(
            nonce: $nonceValue,
            encryptedData: $encryptedValue,
        );

        $entity->$rawDataSetter($decryptedValue);
        $this->setPreviousValue(
            entity: $entity,
            propertyName: $propertyName,
            value: $decryptedValue,
        );
    }

    private function setPreviousValue(
        object $entity,
        string $propertyName,
        #[\SensitiveParameter]
        ?string $value,
    ): void {
        // a property is added on the fly
        // that is not the cleanest but the easiest way to keep track of updated value
        $previousPropertyName = 'doctrinePrevious' . $propertyName;
        $entity->$previousPropertyName = $value;
    }

    private function getPreviousValue(
        object $entity,
        string $propertyName,
    ): ?string {
        $previousPropertyName = 'doctrinePrevious' . $propertyName;

        if (! property_exists($entity, $previousPropertyName)) {
            return null;
        }

        return $entity->$previousPropertyName;
    }
}
