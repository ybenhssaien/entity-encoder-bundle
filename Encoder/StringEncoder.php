<?php

namespace Ybenhssaien\EntityEncoderBundle\Encoder;

class StringEncoder implements EncoderInterface
{
    const OPENSSL_CIPHER = 'AES-256-CBC';

    public static function getSalt(): ?string
    {
        return getenv('ENCODE_KEY') ?: $_ENV['ENCODE_KEY'] ?? null;
    }

    public static function encode(string $text): string
    {
        $nonce = static::getNonce();

        if (\extension_loaded('openssl')) {
            return \bin2hex(
                $nonce.\openssl_encrypt(
                    $text,
                    self::OPENSSL_CIPHER,
                    self::getSalt(),
                    OPENSSL_RAW_DATA,
                    $nonce
                )
            );
        }

        throw new \ErrorException('To encrypt/decrypt data, you need to install php extension "openssl"');
    }

    public static function decode(string $text): string
    {
        $nonceLength = static::getNonceLength();
        $text = \hex2bin($text);
        $nonce = mb_substr($text, 0, $nonceLength, '8bit');
        $text = mb_substr($text, $nonceLength, null, '8bit');

        if (\extension_loaded('openssl')) {
            return openssl_decrypt($text, self::OPENSSL_CIPHER, self::getSalt(), OPENSSL_RAW_DATA, $nonce);
        }

        throw new \ErrorException('To encrypt/decrypt data, you need to install php extension "openssl"');
    }

    protected static function getNonce(): string
    {
        $length = static::getNonceLength();

        try {
            return \random_bytes($length);
        } catch (\Exception $e) {
            return mb_substr(\implode('', \array_fill(1, $length, \rand())), 0, $length);
        }
    }

    protected static function getNonceLength(): int
    {
        if (\extension_loaded('openssl')) {
            return openssl_cipher_iv_length(self::OPENSSL_CIPHER);
        }

        return 24;
    }
}
