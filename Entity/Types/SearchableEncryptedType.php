<?php

namespace Ybenhssaien\EntityEncoderBundle\Entity\Types;

use Ybenhssaien\EntityEncoderBundle\Encoder\SearchableEncoder;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class SearchableEncryptedType extends EncryptedType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return \is_string($value) ? SearchableEncoder::encode($value) : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return \is_string($value) ? SearchableEncoder::decode($value) : $value;
    }

    public function getName()
    {
        return 'encrypted_searchable';
    }
}
