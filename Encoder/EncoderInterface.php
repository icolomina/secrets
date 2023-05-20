<?php

namespace Ict\Secrets\Encoder;

use Ict\Secrets\Model\EncryptionKeys;

interface EncoderInterface
{
    public function generateKeys(): EncryptionKeys;
    public function encode(string $value, EncryptionKeys $encryptionKeys): string;
    public function decode(string $encodedValue, EncryptionKeys $encryptionKeys): ?string;
}
