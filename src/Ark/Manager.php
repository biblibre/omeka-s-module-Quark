<?php

namespace Quark\Ark;

use Doctrine\ORM\EntityManager;
use Omeka\Api\Adapter\Manager as ApiAdapterManager;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class Manager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ApiAdapterManager
     */
    protected $apiAdapterManager;

    public function __construct(EntityManager $entityManager, ApiAdapterManager $apiAdapterManager)
    {
        $this->entityManager = $entityManager;
        $this->apiAdapterManager = $apiAdapterManager;
    }

    public function getResourceFromArk(string $ark): ?AbstractResourceEntityRepresentation
    {
        $em = $this->getEntityManager();

        $dctermsVocabulary = $em->getRepository('Omeka\Entity\Vocabulary')
            ->findOneBy(['prefix' => 'dcterms']);
        $identifierProperty = $em->getRepository('Omeka\Entity\Property')
            ->findOneBy(['vocabulary' => $dctermsVocabulary->getId(), 'localName' => 'identifier']);

        $value = $em->getRepository('Omeka\Entity\Value')
            ->findOneBy(['property' => $identifierProperty->getId(), 'value' => $ark]);

        if ($value) {
            $resource = $value->getResource();
            $adapter = $this->getApiAdapterManager()->get($resource->getResourceName());
            $representation = $adapter->getRepresentation($resource);

            return $representation;
        }
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function getApiAdapterManager(): ApiAdapterManager
    {
        return $this->apiAdapterManager;
    }
}
