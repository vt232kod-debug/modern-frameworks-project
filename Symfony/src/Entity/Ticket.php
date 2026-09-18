<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'tickets')]
#[ORM\UniqueConstraint(name: 'uniq_ticket_seat', columns: ['screening_id', 'seat_row', 'seat_number'])]
#[UniqueEntity(fields: ['screening', 'seatRow', 'seatNumber'], message: 'This seat is already taken for the screening.', errorPath: 'seatNumber')]
class Ticket
{
    public const STATUSES = ['reserved', 'paid', 'cancelled'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ticket:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'screeningId is required.')]
    #[Groups(['ticket:read'])]
    private ?Screening $screening = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'customerId is required.')]
    #[Groups(['ticket:read'])]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?int $seatRow = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?int $seatNumber = null;

    // Stored as DECIMAL (string in PHP); exposed through getPrice()/setPrice() as a number
    #[ORM\Column(name: 'price', type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $priceAmount = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUSES)]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?string $status = 'reserved';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[Groups(['ticket:read'])]
    private ?\DateTimeImmutable $purchasedAt = null;

    public function __construct()
    {
        $this->purchasedAt = new \DateTimeImmutable();
    }

    #[Assert\Callback]
    public function validateSeat(ExecutionContextInterface $context): void
    {
        $hall = $this->screening?->getHall();
        if ($hall === null) {
            return;
        }
        if ($this->seatRow !== null && $this->seatRow > $hall->getRowsCount()) {
            $context->buildViolation('Hall "{{ hall }}" has only {{ max }} rows.')
                ->setParameters(['{{ hall }}' => $hall->getName(), '{{ max }}' => (string) $hall->getRowsCount()])
                ->atPath('seatRow')
                ->addViolation();
        }
        if ($this->seatNumber !== null && $this->seatNumber > $hall->getSeatsPerRow()) {
            $context->buildViolation('Hall "{{ hall }}" has only {{ max }} seats per row.')
                ->setParameters(['{{ hall }}' => $hall->getName(), '{{ max }}' => (string) $hall->getSeatsPerRow()])
                ->atPath('seatNumber')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScreening(): ?Screening
    {
        return $this->screening;
    }

    public function setScreening(?Screening $screening): static
    {
        $this->screening = $screening;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getSeatRow(): ?int
    {
        return $this->seatRow;
    }

    public function setSeatRow(?int $seatRow): static
    {
        $this->seatRow = $seatRow;

        return $this;
    }

    public function getSeatNumber(): ?int
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(?int $seatNumber): static
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[Groups(['ticket:read'])]
    public function getPrice(): ?float
    {
        return $this->priceAmount === null ? null : (float) $this->priceAmount;
    }

    #[Groups(['ticket:write'])]
    public function setPrice(float|int|string|null $price): static
    {
        $this->priceAmount = $price === null ? null : (string) $price;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPurchasedAt(): ?\DateTimeImmutable
    {
        return $this->purchasedAt;
    }
}
