<?php

namespace Quark\Router;

use Laminas\Router\Http\RouteInterface;
use Laminas\Router\Http\RouteMatch;
use Laminas\Router\Http\Segment;
use Laminas\Stdlib\RequestInterface as Request;
use Omeka\Api\Representation\ItemSetRepresentation;
use Quark\ArkManager;

class Ark extends Segment implements RouteInterface
{
    /**
     * @var ArkManager;
     */
    protected $arkManager;

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::match()
     * @param  Request      $request
     * @param  int|null $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null, array $options = [])
    {
        $routeMatch = parent::match($request, $pathOffset);
        if (!$routeMatch) {
            return null;
        }

        $naan = $routeMatch->getParam('naan');
        $name = $routeMatch->getParam('name');
        $qualifier = $routeMatch->getParam('qualifier');

        $ark = sprintf('ark:/%s/%s', $naan, $name);
        if ($qualifier) {
            [$subname, $variant] = array_pad(explode('.', $qualifier, 2), 2, null);
            $ark .= '/' . $subname;
        }
        $resource = $this->getArkManager()->getResourceFromArk($ark);
        if (!$resource) {
            return null;
        }

        if ($resource instanceof ItemSetRepresentation && $routeMatch->getParam('__SITE__')) {
            $routeMatch->setParam('controller', 'item');
            $routeMatch->setParam('action', 'browse');
            $routeMatch->setParam('item-set-id', $resource->id());
        } else {
            $routeMatch->setParam('controller', $resource->getControllerName());
            $routeMatch->setParam('action', 'show');
            $routeMatch->setParam('id', $resource->id());
        }

        return $routeMatch;
    }

    public function setArkManager(ArkManager $arkManager)
    {
        $this->arkManager = $arkManager;
    }

    public function getArkManager()
    {
        return $this->arkManager;
    }
}
