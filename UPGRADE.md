# Upgrade from version 1 to 2

In version 1, the encrypted and nonce properties were inverted in database.

The fix introduce a BC BREAK.

When you are upgrading from v1 to v2:
- create a backup of your database !
- add 2 new suffixed columns that will be your new columns
- in your migration, copy the previous nonceData in the new encryptedData
- in your migration, copy the previous encryptedData in the new nonceData
- set the Cryptable attribute nonceProperty to the new column
- set the Cryptable attribute encryptedProperty to the new column
- check that your application is ok
- now you can create a new migration that remove the previous columns

The entity:

```php
#[ORM\Entity()]
class MyClass
{
    #[Cryptable(
        nonceProperty: 'myValueNonceV2', // new column
        encryptedProperty: 'myValueEncryptedV2', // new column
    )]
    private ?string $myValue = null; // this value is not stored in DB

    #[ORM\Column(type: Types::BINARY, nullable: true, length: 2500)]
    private ?string $myValueEncrypted = null;
    #[ORM\Column(type: Types::BINARY, nullable: true)]
    private ?string $myValueNonce = null;

    // new columns
    #[ORM\Column(type: Types::BINARY, nullable: true)]
    private ?string $myValueEncryptedV2 = null;
    #[ORM\Column(type: Types::BINARY, nullable: true)]
    private ?string $myValueNonceV2 = null;

    ...
}
```

The migration:

```sql
    // my_class my_value
    ALTER TABLE my_class ADD my_value_encrypted_v2 BYTEA DEFAULT NULL;
    ALTER TABLE my_class ADD my_value_nonce_v2 BYTEA DEFAULT NULL;

    UPDATE my_class SET my_value_encrypted_v2 = my_value_nonce;
    UPDATE my_class SET my_value_nonce_v2 = my_value_encrypted;

```
