<?php

namespace Ict\Secrets\Model;

class EncryptionKeys
{
    public function __construct(
        public readonly string $decryptionKey,
        public readonly string $encryptionKey
    ){ }
}
