<?php

namespace Ybenhssaien\EntityEncoderBundle\Entity\Types;

use Doctrine\DBAL\Types\StringType;
use Ybenhssaien\EntityEncoderBundle\Encoder\StringEncoder;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class EncryptedType extends StringType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return \is_string($value) ? StringEncoder::encode($value) : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return \is_string($value) ? StringEncoder::decode($value) : $value;
    }

    public function getName()
    {
        return 'encrypted';
    }
}
