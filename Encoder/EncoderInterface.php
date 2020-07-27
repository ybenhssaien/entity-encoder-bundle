<?php

namespace Ybenhssaien\EntityEncoderBundle\Encoder;

interface EncoderInterface
{
    /**
     * Returns the key to be used in encode/decode data.
     */
    public static function getSalt(): ?string;

    /*
     * Encodes and returns encoded $value
     */
    public static function encode(string $value): string;

    /*
     * Decodes $value already encoded by encode() method
     */
    public static function decode(string $value): string;
}
