<?php

namespace Quark;

use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Module\AbstractModule;
use Omeka\Api\Adapter\ItemAdapter;
use Omeka\Api\Adapter\ItemSetAdapter;
use Omeka\Api\Adapter\MediaAdapter;
use Omeka\Entity\Item;
use Omeka\Entity\Media;
use Omeka\Entity\Property;
use Omeka\Entity\Resource;
use Omeka\Entity\Value;
use Quark\Form\ConfigForm;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $this->addRoutes();
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        $settings = $this->getServiceLocator()->get('Omeka\Settings');

        $form = $formElementManager->get(ConfigForm::class);
        $form->setData([
            'naan' => $settings->get('quark_naan', '99999'),
            'shoulder' => $settings->get('quark_shoulder', ''),
        ]);

        return $renderer->formCollection($form, false);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        $settings = $this->getServiceLocator()->get('Omeka\Settings');

        $form = $formElementManager->get(ConfigForm::class);
        $form->setData($controller->params()->fromPost());
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        $formData = $form->getData();
        $settings->set('quark_naan', $formData['naan']);
        $settings->set('quark_shoulder', $formData['shoulder']);

        return true;
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $onResourceSave = [$this, 'onResourceSave'];
        $sharedEventManager->attach(ItemSetAdapter::class, 'api.create.post', $onResourceSave);
        $sharedEventManager->attach(ItemSetAdapter::class, 'api.update.post', $onResourceSave);
        $sharedEventManager->attach(ItemAdapter::class, 'api.create.post', $onResourceSave);
        $sharedEventManager->attach(ItemAdapter::class, 'api.update.post', $onResourceSave);
        $sharedEventManager->attach(MediaAdapter::class, 'api.create.post', $onResourceSave);
        $sharedEventManager->attach(MediaAdapter::class, 'api.update.post', $onResourceSave);
    }

    public function onResourceSave(Event $event)
    {
        $resource = $event->getParam('response')->getContent();
        $this->assignArk($resource);
        $this->getServiceLocator()->get('Omeka\EntityManager')->flush();
    }

    protected function assignArk(Resource $resource, bool $force = false)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        $arkManager = $services->get('Quark\ArkManager');
        $em = $services->get('Omeka\EntityManager');

        $values = $resource->getValues();
        $arkValues = $arkManager->getArkValues($resource);
        $hasArk = !$arkValues->isEmpty();
        if ($force) {
            foreach ($arkValues as $arkValue) {
                $values->removeElement($arkValue);
            }
            $hasArk = false;
        }

        if (!$hasArk) {
            if ($resource instanceof Media) {
                $itemArkValues = $arkManager->getArkValues($resource->getItem());
                if (!$itemArkValues->isEmpty()) {
                    $itemArk = $itemArkValues->first()->getValue();
                    $ark = sprintf('%s/%s', $itemArk, $arkManager->createArkName());
                }
            } else {
                $naan = $settings->get('quark_naan', '99999');
                $ark = sprintf('ark:/%05d/%s', $naan, $arkManager->createArkName());
            }

            if ($ark) {
                $dctermsIdentifierProperty = $em->getReference(Property::class, $arkManager->getDctermsIdentifierProperty()->getId());
                $value = new Value();
                $value->setResource($resource);
                $value->setProperty($dctermsIdentifierProperty);
                $value->setType('literal');
                $value->setValue($ark);
                $values->add($value);
            }
        }

        if ($resource instanceof Item) {
            $forceAssignArkForMedia = !$hasArk;
            foreach ($resource->getMedia() as $media) {
                $this->assignArk($media, $forceAssignArkForMedia);
            }
        }
    }

    protected function addRoutes()
    {
        $services = $this->getServiceLocator();
        $router = $services->get('Router');
        if (!$router instanceof \Laminas\Router\Http\TreeRouteStack) {
            return;
        }

        $arkManager = $services->get('Quark\ArkManager');
        $route = Router\Ark::factory([
            'route' => '/s/:site-slug/:prefix/:naan/:name[/:qualifier]',
            'constraints' => [
                'site-slug' => '[a-zA-Z0-9_-]+',
                'prefix' => 'ark:',
            ],
            'defaults' => [
                '__NAMESPACE__' => 'Omeka\Controller\Site',
                '__SITE__' => true,
            ],
        ]);
        $route->setArkManager($arkManager);
        $router->addRoute('site-ark', $route);

        $route = Router\Ark::factory([
            'route' => '/admin/:prefix/:naan/:name[/:qualifier]',
            'constraints' => [
                'prefix' => 'ark:',
            ],
            'defaults' => [
                '__NAMESPACE__' => 'Omeka\Controller\Admin',
                '__ADMIN__' => true,
            ],
        ]);
        $route->setArkManager($arkManager);
        $router->addRoute('admin-ark', $route);
    }
}
