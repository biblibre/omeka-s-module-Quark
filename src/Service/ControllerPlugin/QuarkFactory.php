<?php

namespace Quark\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Quark\Mvc\Controller\Plugin\Quark;
use Zend\ServiceManager\Factory\FactoryInterface;

class QuarkFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Quark(
            $services->get('Quark\ArkManager'),
        );
    }
}
