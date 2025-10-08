<?php

namespace Dayploy\DoctrineExtensionsBundle\Tests\Entity;

use Dayploy\DoctrineExtensionsBundle\Cryptable\Cryptable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class MyClass
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[Cryptable(
        nonceProperty: 'myValueNonce',
        encryptedProperty: 'myValueEncrypted',
    )]
    private ?string $myValue = null;

    #[ORM\Column(type: Types::BINARY, nullable: true, length: 2500)]
    private ?string $myValueEncrypted = null;
    #[ORM\Column(type: Types::BINARY, nullable: true)]
    private ?string $myValueNonce = null;

    public function getMyValue(): ?string
    {
        return $this->myValue;
    }

    public function setMyValue(?string $myValue): self
    {
        $this->myValue = $myValue;
        return $this;
    }

    public function getMyValueEncrypted(): ?string
    {
        return $this->myValueEncrypted;
    }

    public function setMyValueEncrypted(?string $myValueEncrypted): self
    {
        $this->myValueEncrypted = $myValueEncrypted;
        return $this;
    }

    public function getMyValueNonce(): ?string
    {
        return $this->myValueNonce;
    }

    public function setMyValueNonce(?string $myValueNonce): self
    {
        $this->myValueNonce = $myValueNonce;
        return $this;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
}
