<?php

namespace Quark;

use Omeka\Module\AbstractModule;
use Omeka\Api\Adapter\ItemAdapter;
use Omeka\Api\Adapter\ItemSetAdapter;
use Omeka\Api\Adapter\MediaAdapter;
use Omeka\Entity\Item;
use Omeka\Entity\Media;
use Omeka\Entity\Resource;
use Omeka\Entity\Value;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $services)
    {
        $connection = $services->get('Omeka\Connection');
        $connection->exec('CREATE TABLE quark_identifier (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_4536A0925E237E06 (name), UNIQUE INDEX UNIQ_4536A09289329D25 (resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $connection->exec('ALTER TABLE quark_identifier ADD CONSTRAINT FK_4536A09289329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $connection = $services->get('Omeka\Connection');
        $connection->exec('DROP TABLE IF EXISTS quark_identifier');
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, 'Quark\Controller\Site\Ark');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $onResourceSave = [$this, 'onResourceSave'];
        $sharedEventManager->attach(ItemSetAdapter::class, 'api.create.post', $onResourceSave);
        $sharedEventManager->attach(ItemSetAdapter::class, 'api.update.post', $onResourceSave);
        $sharedEventManager->attach(ItemAdapter::class,    'api.create.post', $onResourceSave);
        $sharedEventManager->attach(ItemAdapter::class,    'api.update.post', $onResourceSave);
        $sharedEventManager->attach(MediaAdapter::class,   'api.create.post', $onResourceSave);
        $sharedEventManager->attach(MediaAdapter::class,   'api.update.post', $onResourceSave);
    }

    public function onResourceSave(Event $event)
    {
        $resource = $event->getParam('response')->getContent();
        $this->assignArk($resource);
    }

    protected function assignArk(Resource $resource, bool $force = false)
    {
        $em = $this->getServiceLocator()->get('Omeka\EntityManager');

        $values = $resource->getValues();
        $arkValues = $this->getArkValues($resource);
        $hasArk = !$arkValues->isEmpty();
        if ($force) {
            foreach ($arkValues as $arkValue) {
                $values->removeElement($arkValue);
            }
            $hasArk = false;
        }

        if (!$hasArk) {
            if ($resource instanceof Media) {
                $itemArkValues = $this->getArkValues($resource->getItem());
                if (!$itemArkValues->isEmpty()) {
                    $itemArk = $itemArkValues->first()->getValue();
                    $ark  = sprintf('%s/%s', $itemArk, $this->uuid());
                }
            } else {
                $ark = sprintf('ark:/99999/%s', $this->uuid());
            }

            if ($ark) {
                $dctermsVocabulary = $em->getRepository('Omeka\Entity\Vocabulary')
                    ->findOneBy(['prefix' => 'dcterms']);
                $identifierProperty = $em->getRepository('Omeka\Entity\Property')
                    ->findOneBy(['vocabulary' => $dctermsVocabulary->getId(), 'localName' => 'identifier']);

                $value = new Value();
                $value->setResource($resource);
                $value->setProperty($identifierProperty);
                $value->setType('literal');
                $value->setValue($ark);
                $values->add($value);

                if ($resource instanceof Item) {
                    foreach ($resource->getMedia() as $media) {
                        $this->assignArk($media, true);
                    }
                }

                $em->flush();
            }
        }
    }

    protected function getArkValues(Resource $resource)
    {
        $em = $this->getServiceLocator()->get('Omeka\EntityManager');

        $dctermsVocabulary = $em->getRepository('Omeka\Entity\Vocabulary')
            ->findOneBy(['prefix' => 'dcterms']);
        $identifierProperty = $em->getRepository('Omeka\Entity\Property')
            ->findOneBy(['vocabulary' => $dctermsVocabulary->getId(), 'localName' => 'identifier']);

        $isArk = function ($value) use ($identifierProperty) {
            if ($value->getType() !== 'literal') {
                return false;
            }

            if (0 !== strncmp($value->getValue(), 'ark:/', 5)) {
                return false;
            }

            if ($value->getProperty()->getId() !== $identifierProperty->getId()) {
                return false;
            }

            return true;
        };

        return $resource->getValues()->filter($isArk);
    }

    protected function uuid()
    {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }
}
