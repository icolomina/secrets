<?php

namespace Ict\Secrets\Vault;

use Ict\Secrets\Encoder\EncoderInterface;
use Ict\Secrets\Model\EncryptionKeys;

class RedisVaultStorage implements VaultStorageInterface
{
    private ?EncryptionKeys $encryptionKeys = null;

    public function __construct(
        private readonly mixed $rdsVault,
        private readonly EncoderInterface $keysEncoder
    ){ }

    public function storeKeys(string $name, bool $force = false): self
    {
        if(!$force && ($this->rdsVault->exists("{$name}:dec") || $this->rdsVault->exists("{$name}:enc") ) ){
            return $this;
        }

        $this->rdsVault->del(["{$name}:dec", "{$name}:enc"]);

        $encryptionKeys = $this->keysEncoder->generateKeys();
        $this->rdsVault->set("{$name}:dec", $encryptionKeys->decryptionKey);
        $this->rdsVault->set("{$name}:enc", $encryptionKeys->encryptionKey);

        $this->encryptionKeys = $encryptionKeys;
        return $this;
    }

    public function storeSecret(string $name, string $value): void
    {
        $this->rdsVault->set($name, $this->keysEncoder->encode($value, $this->encryptionKeys));
    }

    public function storeSecrets(array $secrets): void
    {
        foreach ($secrets as $name => $value) {
            $this->storeSecret($name, $value);
        }
    }


    public function getSecret(string $name): ?string
    {
        return $this->keysEncoder->decode($this->rdsVault->get($name), $this->encryptionKeys);
    }

    public function loadKeys(string $name): self
    {
        $decryptionKey = $this->rdsVault->get("{$name}:dec");
        $encryptionKey = $this->rdsVault->get("{$name}:enc");

        $this->encryptionKeys = new EncryptionKeys($decryptionKey, $encryptionKey);
        return $this;
    }
}
