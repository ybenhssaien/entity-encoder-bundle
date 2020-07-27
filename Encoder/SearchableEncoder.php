<?php

namespace Ybenhssaien\EntityEncoderBundle\Encoder;

/**
 * Generate a stable nonce based on the value to be encrypted  which can make it searchable.
 *
 * Class SearchableEncoder
 */
class SearchableEncoder extends StringEncoder
{
    private static $nonce;

    public static function encode(string $text): string
    {
        /* Use the passed text to generate nonce */
        self::$nonce = self::generateNonce($text);

        $encoded = parent::encode($text);

        /* Reset nonce */
        self::$nonce = null;

        return $encoded;
    }

    protected static function getNonce(): string
    {
        return self::$nonce ?: parent::getNonce();
    }

    private static function generateNonce(string $text): string
    {
        $length = static::getNonceLength();

        /* Check the text lenght to be the same as nonce length */
        /* Fill the remaining length by 0 */
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length) : \str_pad($text, $length, '0');
    }
}
