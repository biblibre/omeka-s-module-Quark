<?php

namespace Quark\Mvc\Controller\Plugin;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Quark\Ark\Manager as ArkManager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Quark extends AbstractPlugin
{
    /**
     * @var ArkManager
     */
    protected $arkManager;

    public function __construct(ArkManager $arkManager)
    {
        $this->arkManager = $arkManager;
    }

    public function getFullArkAndVariantFromParams(): array
    {
        $params = $this->getController()->params();
        $naan = $params->fromRoute('naan');
        $name = $params->fromRoute('name');
        $qualifier = $params->fromRoute('qualifier');

        $ark = sprintf('ark:/%s/%s', $naan, $name);
        if ($qualifier) {
            list($subname, $variant) = array_pad(explode('.', $qualifier, 2), 2, null);
            $ark .= '/' . $subname;
        }

        return [$ark, $variant ?? null];
    }

    public function getResourceFromArk(string $ark): ?AbstractResourceEntityRepresentation
    {
        return $this->arkManager->getResourceFromArk($ark);
    }
}
