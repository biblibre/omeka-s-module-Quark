<?php

namespace Quark\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Resource;

/**
 * @Entity
 */
class QuarkIdentifier extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="Omeka\Entity\Resource")
     * @JoinColumn(nullable=false, unique=true, onDelete="CASCADE")
     */
    protected $resource;

    /**
     * @Column(unique=true)
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
