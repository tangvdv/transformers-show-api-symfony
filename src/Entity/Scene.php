<?php

namespace App\Entity;

use App\Repository\SceneRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: SceneRepository::class)]
#[ORM\Table(name: '`scene`')]
class Scene
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull()]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotNull()]
    private ?DateTime $start_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotNull()]
    private ?DateTime $end_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?DateTime $duration = null;

    #[ORM\Column(type: 'integer')]
    private ?int $timestamp = null;

    #[ORM\ManyToMany(targetEntity: Bot::class, inversedBy: 'scenes')]
    #[ORM\JoinTable(name: 'bot_scene')]
    private ?Collection $bots = null;

    #[ORM\ManyToMany(targetEntity: Artefact::class, inversedBy: "scenes")]
    #[ORM\JoinTable(name: 'artefact_scene')]
    private ?Collection $artefacts = null;

    #[ORM\ManyToMany(targetEntity: Human::class, inversedBy: "scenes")]
    #[ORM\JoinTable(name: 'human_scene')]
    private ?Collection $humans = null;

    public function __construct()
    {
        $this->artefacts = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartTime(): ?string
    {
        return $this->start_time->format("H:i:s");
    }

    public function setStartTime(string $start_time): static
    {
        $time = DateTime::createFromFormat("H:i:s", $start_time);
        $this->start_time = $time;

        return $this;
    }

    public function getEndTime(): ?string
    {
        return $this->end_time->format("H:i:s");
    }

    public function setEndTime(string $end_time): static
    {
        $time = DateTime::createFromFormat("H:i:s", $end_time);
        $this->end_time = $time;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration->format("H:i:s");
    }

    public function setDuration(DateTime $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getTimeStamp(): ?int
    {
        return $this->timestamp;
    }

    public function setTimeStamp(int $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function calculateDuration(){
        if($this->start_time && $this->end_time){
            $diff = $this->end_time->getTimeStamp() - $this->start_time->getTimeStamp();
            $time = new DateTime();
            $time->setTimeStamp($diff);

            $hours = (int)$time->format("H");
            $minutes = (int)$time->format("i");
            $seconds = (int)$time->format("s");

            $timestamp = $hours * 3600 + $minutes * 60 + $seconds;

            $this->setDuration($time);
            $this->setTimeStamp($timestamp);
        }
    }

    /**
     * @return Collection<int, Bot>
     */
    public function getBots(): ?Collection
    {
        return $this->bots;
    }

    public function addBot(Bot $bot): static
    {
        if (!$this->bots->contains($bot)) {
            $this->bots->add($bot);
        }

        return $this;
    }

    public function removeBot(Bot $bot): static
    {
        $this->bots->removeElement($bot);

        return $this;
    }

    /**
     * @return Collection<int, Artefact>
     */
    public function getArtefacts(): ?Collection
    {
        return $this->artefacts;
    }

    public function addArtefact(Artefact $artefact): static
    {
        if (!$this->artefacts->contains($artefact)) {
            $this->artefacts->add($artefact);
        }

        return $this;
    }

    public function removeArtefact(Artefact $artefact): static
    {
        $this->artefacts->removeElement($artefact);

        return $this;
    }

    /**
     * @return Collection<int, Human>
     */
    public function getHumans(): ?Collection
    {
        return $this->humans;
    }

    public function addHuman(Human $human): static
    {
        if (!$this->humans->contains($human)) {
            $this->humans->add($human);
        }

        return $this;
    }

    public function removeHuman(Human $human): static
    {
        $this->humans->removeElement($human);

        return $this;
    }
}
