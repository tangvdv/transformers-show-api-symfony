<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[UniqueEntity('client_id')]
#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\EntityListeners(['App\EntityListener\ApplicationListener'])]
#[ORM\Table(name: '`application`')]
class Application implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 45)]
    #[Assert\NotBlank()]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank()]
    private ?string $client_id = null;

    private ?string $plain_client_secret = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank()]
    private ?string $client_secret = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull()]
    private ?\DateTime $created_at = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull()]
    private ?\DateTime $updated_at = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $max_request = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Stat::class, mappedBy: 'application')]
    private Collection $stats;

    public function __construct()
    {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
        $this->stats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplicationName(): ?string
    {
        return $this->name;
    }

    public function setApplicationName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->client_id;
    }

    public function setClientId(string $client_id): static
    {
        $this->client_id = $client_id;

        return $this;
    }

    public function getPlainClientSecret(): ?string
    {
        return $this->plain_client_secret;
    }

    public function setPlainClientSecret(string $plain_client_secret): static
    {
        $this->plain_client_secret = $plain_client_secret;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getMaxRequest(): ?int
    {
        return $this->max_request;
    }

    public function setMaxRequest(?int $max_request): static
    {
        $this->max_request = $max_request;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

     /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_APPLICATION';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->client_secret;
    }

    public function setPassword(string $client_secret): self
    {
        $this->client_secret = $client_secret;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->client_id;
    }

    /**
     * @return Collection<int, Stat>
     */
    public function getStats(): Collection
    {
        return $this->stats;
    }

    public function addStat(Stat $stat): static
    {
        if (!$this->stats->contains($stat)) {
            $this->stats->add($stat);
            $stat->setApplication($this);
        }

        return $this;
    }

    public function removeStat(Stat $stat): static
    {
        if ($this->stats->removeElement($stat)) {
            // set the owning side to null (unless already changed)
            if ($stat->getApplication() === $this) {
                $stat->setApplication(null);
            }
        }

        return $this;
    }
}
