<?php

namespace Quark\Service\Ark;

use Interop\Container\ContainerInterface;
use Quark\Ark\Manager as ArkManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class ManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ArkManager(
            $services->get('Omeka\EntityManager'),
            $services->get('Omeka\ApiAdapterManager'),
        );
    }
}
