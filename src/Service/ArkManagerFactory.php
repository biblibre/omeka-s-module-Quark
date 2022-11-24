<?php

namespace Quark\Service;

use Interop\Container\ContainerInterface;
use Quark\ArkManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ArkManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ArkManager(
            $services->get('Omeka\EntityManager'),
            $services->get('Omeka\ApiAdapterManager'),
            $services->get('Omeka\Settings'),
        );
    }
}
