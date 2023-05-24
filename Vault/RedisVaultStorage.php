<?php

namespace Ict\Secrets\Vault;

use Ict\Secrets\Encoder\EncoderInterface;
use Ict\Secrets\Model\EncryptionKeys;

class RedisVaultStorage implements VaultStorageInterface
{
    private ?EncryptionKeys $encryptionKeys = null;

    public function __construct(
        private readonly mixed $rdsVault,
        private readonly EncoderInterface $keysEncoder,
        private readonly string $hashAlg
    ){ }

    public function storeKeys(string $name, bool $force = false): self
    {
        $hashedEncKeyName = hash($this->hashAlg,"{$name}:enc");
        $hashedDecKeyName = hash($this->hashAlg,"{$name}:dec");

        if(!$force && ($this->rdsVault->exists($hashedDecKeyName) || $this->rdsVault->exists($hashedEncKeyName )) ){
            return $this;
        }

        $this->rdsVault->del([$hashedDecKeyName, $hashedEncKeyName]);

        $encryptionKeys = $this->keysEncoder->generateKeys();
        $this->rdsVault->set($hashedDecKeyName, $encryptionKeys->decryptionKey);
        $this->rdsVault->set($hashedEncKeyName, $encryptionKeys->encryptionKey);

        $this->encryptionKeys = $encryptionKeys;
        return $this;
    }

    public function storeSecret(string $name, string $value): void
    {
        $this->rdsVault->set(hash($this->hashAlg, $name), $this->keysEncoder->encode($value, $this->encryptionKeys));
    }

    public function storeSecrets(array $secrets): void
    {
        foreach ($secrets as $name => $value) {
            $this->storeSecret($name, $value);
        }
    }


    public function getSecret(string $name): ?string
    {
        return $this->keysEncoder->decode($this->rdsVault->get(hash($this->hashAlg, $name)), $this->encryptionKeys);
    }

    public function loadKeys(string $name): self
    {
        $decryptionKey = $this->rdsVault->get(hash($this->hashAlg, "{$name}:dec"));
        $encryptionKey = $this->rdsVault->get(hash($this->hashAlg, "{$name}:enc"));

        $this->encryptionKeys = new EncryptionKeys($decryptionKey, $encryptionKey);
        return $this;
    }

    public function removeSecret(string $name): void
    {
        $this->rdsVault->del([hash($this->hashAlg, $name)]);
    }

    public function removeKeys(string $name): void
    {
        $hashedEncKeyName = hash($this->hashAlg,"{$name}:enc");
        $hashedDecKeyName = hash($this->hashAlg,"{$name}:dec");

        $this->rdsVault->del([$hashedEncKeyName, $hashedDecKeyName]);
    }
}
