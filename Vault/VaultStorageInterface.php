<?php

namespace Ict\Secrets\Vault;

interface VaultStorageInterface
{
    public function storeKeys(string $name): self;
    public function storeSecret(string $name, string $value): void;
    public function storeSecrets(array $secrets): void;
    public function getSecret(string $name): ?string;
    public function loadKeys(string $name): self;
}
