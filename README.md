# DayployDoctrineExtensionsBundle

Encrypt your sensitive data in your database.

# How it works

Your property is in your entity but not stored directly in the database.

Before persisting in db, it is encrypted using a `nonce` and the encrypted value is stored in the `encryptedValue`.

The reverse process is done when the entity is loaded from the db.

Anyone that acces your db can not read the value without the encryption key.

# Entity update

In your entity, add your cryptable property. This property is not stored in database.

It can be nullable or not, it depends of your context, but it must be a string.

In this example: $myValue.

The nonce and encrypted value must be stored in binary.

```php
#[ORM\Entity()]
class MyClass
{
    #[Cryptable(
        nonceProperty: 'myValueNonce',
        encryptedProperty: 'myValueEncrypted',
    )]
    private ?string $myValue = null; // this value is not stored in DB

    #[ORM\Column(type: Types::BINARY, nullable: true, length: 2500)]
    private ?string $myValueEncrypted = null;
    #[ORM\Column(type: Types::BINARY, nullable: true)]
    private ?string $myValueNonce = null;

    ...
}
```

# Configuration

## Add the bundle to your project

```bash
composer require dayploy/doctrine-extensions-bundle
```

## Enable the bundle

Add the bundle to `config/bundles.php`:

```php
<?php

return [
    ...
    Dayploy\DoctrineExtensionsBundle\DayployDoctrineExtensionsBundle::class => ['all' => true],
];
```

## Encryption Generation

First, generate a new DOCTRINE_ENCRYPTION_KEY:

```bash
./bin/console dayploy:doctrine-extensions:generate
```

put the generated DOCTRINE_ENCRYPTION_KEY in your .env

> Of course, use an encryption key per environment !
> Do not lose this encryption key, without it, your data is lost
