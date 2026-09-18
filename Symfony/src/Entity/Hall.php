<?php

namespace App\Entity;

use App\Repository\HallRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HallRepository::class)]
#[ORM\Table(name: 'halls')]
#[UniqueEntity(fields: ['name'])]
class Hall
{
    public const TYPES = ['2D', '3D', 'IMAX', 'VIP'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['hall:read', 'screening:read', 'ticket:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['hall:read', 'hall:write', 'screening:read', 'ticket:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['hall:read', 'hall:write', 'screening:read'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 50)]
    #[Groups(['hall:read', 'hall:write'])]
    private ?int $rowsCount = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 50)]
    #[Groups(['hall:read', 'hall:write'])]
    private ?int $seatsPerRow = null;

    /**
     * @var Collection<int, Screening>
     */
    #[ORM\OneToMany(targetEntity: Screening::class, mappedBy: 'hall')]
    private Collection $screenings;

    public function __construct()
    {
        $this->screenings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRowsCount(): ?int
    {
        return $this->rowsCount;
    }

    public function setRowsCount(?int $rowsCount): static
    {
        $this->rowsCount = $rowsCount;

        return $this;
    }

    public function getSeatsPerRow(): ?int
    {
        return $this->seatsPerRow;
    }

    public function setSeatsPerRow(?int $seatsPerRow): static
    {
        $this->seatsPerRow = $seatsPerRow;

        return $this;
    }

    #[Groups(['hall:read'])]
    public function getCapacity(): int
    {
        return (int) $this->rowsCount * (int) $this->seatsPerRow;
    }

    /**
     * @return Collection<int, Screening>
     */
    public function getScreenings(): Collection
    {
        return $this->screenings;
    }
}
