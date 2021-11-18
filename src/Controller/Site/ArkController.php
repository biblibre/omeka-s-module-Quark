<?php

namespace Quark\Controller\Site;

use Omeka\Mvc\Exception\NotFoundException;
use Omeka\Api\Representation\MediaRepresentation;
use Quark\Ark\Manager as ArkManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ArkController extends AbstractActionController
{
    public function indexAction()
    {
        list($ark, $variant) = $this->quark()->getFullArkAndVariantFromParams();
        $resource = $this->quark()->getResourceFromArk($ark);
        if (!$resource) {
            throw new NotFoundException;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if ('?' === substr($requestUri, -1)) {
            $this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
            $view = new ViewModel;
            $view->setTerminal(true);
            $view->setVariable('resource', $resource);

            if ('??' === substr($requestUri, -2)) {
                $view->setTemplate('quark/site/ark/policy');
            } else {
                $view->setTemplate('quark/site/ark/metadata');
            }

            return $view;
        }

        if (isset($variant) && $resource instanceof MediaRepresentation) {
            if ($variant === 'original') {
                $url = $resource->originalUrl();
            } else {
                $url = $resource->thumbnailUrl($variant);
            }
            if ($url) {
                return $this->redirect()->toUrl($url);
            }
        }

        return $this->redirect()->toUrl($resource->siteUrl());
    }
}
