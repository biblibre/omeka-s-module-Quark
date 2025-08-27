<?php

namespace Quark;

use Doctrine\ORM\EntityManager;
use Omeka\Api\Adapter\Manager as ApiAdapterManager;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Entity\Resource;
use Omeka\Entity\Property;
use Omeka\Settings\Settings;

class ArkManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ApiAdapterManager
     */
    protected $apiAdapterManager;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Property
     */
    protected $dctermsIdentifierProperty;

    public function __construct(EntityManager $entityManager, ApiAdapterManager $apiAdapterManager, Settings $settings)
    {
        $this->entityManager = $entityManager;
        $this->apiAdapterManager = $apiAdapterManager;
        $this->settings = $settings;
    }

    public function getResourceFromArk(string $ark): ?AbstractResourceEntityRepresentation
    {
        $em = $this->getEntityManager();

        $identifierProperty = $this->getDctermsIdentifierProperty();

        $value = $em->getRepository('Omeka\Entity\Value')
            ->findOneBy(['property' => $identifierProperty->getId(), 'value' => $ark]);

        if ($value) {
            $resource = $value->getResource();
            if ($resource) {
                $adapter = $this->getApiAdapterManager()->get($resource->getResourceName());
                $representation = $adapter->getRepresentation($resource);

                return $representation;
            }
        }

        return null;
    }

    public function getArkValues(Resource $resource)
    {
        $identifierProperty = $this->getDctermsIdentifierProperty();

        $isArk = function ($value) use ($identifierProperty) {
            if ($value->getType() !== 'literal') {
                return false;
            }

            if ($value->getProperty()->getId() !== $identifierProperty->getId()) {
                return false;
            }

            if (0 !== strncmp($value->getValue(), 'ark:/', 5)) {
                return false;
            }

            return true;
        };

        return $resource->getValues()->filter($isArk);
    }

    public function createArkName(): string
    {
        $uuid = $this->uuid();
        $shoulder = $this->settings->get('quark_shoulder', '');
        $uuid_base64 = sodium_bin2base64($uuid, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        $arkName = $shoulder . str_replace('-', '~', $uuid_base64);

        return $arkName;
    }

    public function getDctermsIdentifierProperty()
    {
        if (!isset($this->dctermsIdentifierProperty)) {
            $em = $this->getEntityManager();

            $dctermsVocabulary = $em->getRepository('Omeka\Entity\Vocabulary')
                ->findOneBy(['prefix' => 'dcterms']);
            $identifierProperty = $em->getRepository('Omeka\Entity\Property')
                ->findOneBy(['vocabulary' => $dctermsVocabulary->getId(), 'localName' => 'identifier']);

            $this->dctermsIdentifierProperty = $identifierProperty;
        }

        return $this->dctermsIdentifierProperty;
    }

    protected function uuid()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return $data;
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function getApiAdapterManager(): ApiAdapterManager
    {
        return $this->apiAdapterManager;
    }

    protected function getSettings(): Settings
    {
        return $this->settings;
    }
}
