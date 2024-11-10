<?php

namespace App\Entity;

use App\Repository\StatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: StatRepository::class)]
#[ORM\Table(name: '`stat`')]
class Stat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 45)]
    #[Assert\NotBlank()]
    private ?string $endpoint = null;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\NotBlank()]
    private ?string $method = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull()]
    private ?\DateTimeImmutable $request_at = null;

    #[ORM\ManyToOne(inversedBy: 'stats')]
    private ?Application $application = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getRequestAt(): ?\DateTimeImmutable
    {
        return $this->request_at;
    }

    public function setRequestAt(\DateTimeImmutable $request_at): static
    {
        $this->request_at = $request_at;

        return $this;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): static
    {
        $this->application = $application;

        return $this;
    }
}
