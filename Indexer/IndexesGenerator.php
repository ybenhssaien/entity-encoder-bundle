<?php

namespace Ybenhssaien\EntityEncoderBundle\Indexer;

use Ybenhssaien\EntityEncoderBundle\Encoder\EncoderInterface;
use Ybenhssaien\EntityEncoderBundle\Exception\BadParamException;

class IndexesGenerator
{
    protected string $encoder = StringEncoder::class;

    public function setEncoder(string $encoder): self
    {
        try {
            if (! (new \ReflectionClass($encoder))->implementsInterface(EncoderInterface::class)) {
                throw new BadParamException('Encoder must implements "%s", check the passed encoder "%s"', EncoderInterface::class, $encoder);
            }
        } catch (\ReflectionException $e) {
            throw new BadParamException('Encoder must be a valid class that implements "%s", check the passed encoder "%s"', EncoderInterface::class, $encoder);
        }

        $this->encoder = $encoder;

        return $this;
    }

    public function generateIndexesStartWith(string $value, int $minChars = 3): array
    {
        $return = [];
        $strLength = mb_strlen($value);

        if ($minChars > $strLength) {
            return $return;
        }

        /**
         * Starts encoding from the $minChars
         * Example : $minChars = 3 / $value = Test
         *  0 - $strToEncode = Te => Before starting foreach
         *  1 - $strToEncode = Tes
         *  2 - $strToEncode = Test
         * Result : 2 indexes lines.
         */
        $strToEncode = mb_substr($value, 0, $minChars - 1);
        foreach (\str_split(mb_substr($value, $minChars - 1)) as $char) {
            $strToEncode .= $char;

            $return[] = $this->encoder::encode($strToEncode);
        }

        return $return;
    }
}
