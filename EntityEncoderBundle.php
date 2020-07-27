<?php

namespace Ybenhssaien\EntityEncoderBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EntityEncoderBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Resources'));
        $loader->load('services.yaml');
        $loader->load('doctrine.yaml');
    }
}
