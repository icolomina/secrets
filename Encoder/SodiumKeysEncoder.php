<?php

namespace Ict\Secrets\Encoder;

use Ict\Secrets\Model\EncryptionKeys;

class SodiumKeysEncoder implements EncoderInterface
{
    /**
     * @throws \SodiumException
     */
    public function generateKeys(): EncryptionKeys
    {
        $decryptionKey = sodium_crypto_box_keypair();
        $encryptionKey = sodium_crypto_box_publickey($decryptionKey);

        return new EncryptionKeys($decryptionKey, $encryptionKey);
    }

    /**
     * @throws \SodiumException
     */
    public function encode(string $value, EncryptionKeys $encryptionKeys): string
    {
        return sodium_crypto_box_seal($value, $encryptionKeys->encryptionKey);
    }

    /**
     * @throws \SodiumException
     */
    public function decode(string $encodedValue, EncryptionKeys $encryptionKeys): ?string
    {
        return sodium_crypto_box_seal_open($encodedValue, $encryptionKeys->decryptionKey);
    }
}
