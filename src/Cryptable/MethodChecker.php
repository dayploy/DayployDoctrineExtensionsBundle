<?php

declare(strict_types=1);

namespace Dayploy\DoctrineExtensionsBundle\Cryptable;

use ReflectionAttribute;

class MethodChecker
{
    private static $checkEntities = [];

    public function check(
        object $entity,
    ): void {
        if (in_array($entity::class, static::$checkEntities)) {
            return;
        }

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

                    $this->checkEntity(
                        entity: $entity,
                        propertyName: $propertyName,
                        encryptedProperty: $encryptedProperty,
                        nonceProperty: $nonceProperty,
                    );
                }
            }
        } while ($reflector = $reflector->getParentClass());
    }

    private function checkEntity(
        object $entity,
        string $propertyName,
        string $encryptedProperty,
        string $nonceProperty,
    ): void {
        $reflector = new \ReflectionClass($entity::class);

        $methods = [];
        $methods[] = 'get' . ucwords($propertyName);
        $methods[] = 'set' . ucwords($propertyName);
        $methods[] = 'get' . ucwords($nonceProperty);
        $methods[] = 'set' . ucwords($nonceProperty);
        $methods[] = 'get' . ucwords($encryptedProperty);
        $methods[] = 'set' . ucwords($encryptedProperty);

        foreach ($methods as $method) {
            if (!$reflector->hasMethod($method)) {
                throw new \Exception('The class '.$entity::class.' is missing the method '.$method);
            }
        }

        static::$checkEntities[] = $entity::class;
    }
}
