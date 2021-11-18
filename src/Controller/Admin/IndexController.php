<?php

namespace Quark\Controller\Admin;

use Omeka\Mvc\Exception\NotFoundException;
use Omeka\Api\Representation\MediaRepresentation;
use Quark\Ark\Manager as ArkManager;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    /**
     * @var ArkManager
     */
    protected $arkManager;

    public function __construct(ArkManager $arkManager)
    {
        $this->arkManager = $arkManager;
    }

    public function indexAction()
    {
        $naan = $this->params()->fromRoute('naan');
        $name = $this->params()->fromRoute('name');
        $qualifier = $this->params()->fromRoute('qualifier');

        $ark = sprintf('ark:/%s/%s', $naan, $name);
        $representation = $this->getArkManager()->getResourceFromArk($ark);
        if (!$representation) {
            throw new NotFoundException;
        }

        if (isset($variant) && $representation instanceof MediaRepresentation) {
            if ($variant === 'original') {
                $url = $representation->originalUrl();
            } else {
                $url = $representation->thumbnailUrl($variant);
            }
            if ($url) {
                return $this->redirect()->toUrl($url);
            }
        }

        return $this->redirect()->toUrl($representation->adminUrl());
    }
}
