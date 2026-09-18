<?php

namespace App\Entity;

use App\Repository\ScreeningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScreeningRepository::class)]
#[ORM\Table(name: 'screenings')]
class Screening
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['screening:read', 'ticket:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'screenings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'movieId is required.')]
    #[Groups(['screening:read', 'ticket:read'])]
    private ?Movie $movie = null;

    #[ORM\ManyToOne(inversedBy: 'screenings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'hallId is required.')]
    #[Groups(['screening:read', 'ticket:read'])]
    private ?Hall $hall = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'])]
    #[Groups(['screening:read', 'screening:write', 'ticket:read'])]
    private ?\DateTimeImmutable $startsAt = null;

    // Stored as DECIMAL (string in PHP); exposed through getPrice()/setPrice() as a number
    #[ORM\Column(name: 'price', type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $priceAmount = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Groups(['screening:read', 'screening:write'])]
    private ?string $language = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'screening')]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): static
    {
        $this->movie = $movie;

        return $this;
    }

    public function getHall(): ?Hall
    {
        return $this->hall;
    }

    public function setHall(?Hall $hall): static
    {
        $this->hall = $hall;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[Groups(['screening:read'])]
    public function getPrice(): ?float
    {
        return $this->priceAmount === null ? null : (float) $this->priceAmount;
    }

    #[Groups(['screening:write'])]
    public function setPrice(float|int|string|null $price): static
    {
        $this->priceAmount = $price === null ? null : (string) $price;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }
}
