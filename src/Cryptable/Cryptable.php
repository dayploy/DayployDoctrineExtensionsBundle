<?php

declare(strict_types=1);

namespace Dayploy\DoctrineExtensionsBundle\Cryptable;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Cryptable
{
    public function __construct(
        private string $nonceProperty,
        private string $encryptedProperty,
    ){
    }
}
