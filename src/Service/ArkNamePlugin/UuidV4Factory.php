<?php

namespace Quark\Service\ArkNamePlugin;

use Interop\Container\ContainerInterface;
use Quark\ArkNamePlugin\UuidV4;
use Zend\ServiceManager\Factory\FactoryInterface;

class UuidV4Factory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $name, array $options = null)
    {
        $settings = $services->get('Omeka\Settings');

        return new UuidV4($settings);
    }
}
