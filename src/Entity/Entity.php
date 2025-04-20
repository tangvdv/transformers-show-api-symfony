<?php

namespace App\Entity;

use App\Repository\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: EntityRepository::class)]
#[ORM\Table(name: '`entity`')]
class Entity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\NotBlank()]
    private ?string $entity_name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $type = null;

    #[ORM\OneToMany(targetEntity: Human::class, mappedBy: "entity")]
    private ?Collection $humans = null;

    #[ORM\OneToMany(targetEntity: Bot::class, mappedBy: "entity")]
    private ?Collection $bots = null;

    public function __construct()
    {
        $this->humans = new ArrayCollection();
        $this->bots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEntityName(): ?string
    {
        return $this->entity_name;
    }

    public function setEntityName(string $entity_name): static
    {
        $this->entity_name = $entity_name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getBots(): ?Collection
    {
        return $this->bots;
    }

    public function getHumans(): ?Collection
    {
        return $this->humans;
    }
}
