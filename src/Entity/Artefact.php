<?php

namespace App\Entity;

use App\Repository\ArtefactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtefactRepository::class)]
#[ORM\Table(name: '`artefact`')]
class Artefact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Entity $entity = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Show $show = null;

    #[ORM\ManyToMany(targetEntity: Scene::class, mappedBy: 'artefacts')]
    private ?Collection $scenes = null;

    public function __construct()
    {
        $this->scenes = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(?Entity $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function getShow(): ?Show
    {
        return $this->show;
    }

    public function setShow(Show $show): static
    {
        $this->show = $show;

        return $this;
    }

    public function removeShow(): static
    {
        $this->show = null;

        return $this;
    }

    /**
     * @return Collection<int, Scene>
     */
    public function getScenes(): ?Collection
    {
        return $this->scenes;
    }

    public function addScene(Scene $scene): static
    {
        if (!$this->scenes->contains($scene)) {
            $this->scenes->add($scene);
        }

        return $this;
    }

    public function removeScene(Scene $scene): static
    {
        $this->scenes->removeElement($scene);

        return $this;
    }
}
