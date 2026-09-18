<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_CLIENT = 'client';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';
    public const ROLES = [self::ROLE_CLIENT, self::ROLE_MANAGER, self::ROLE_ADMIN];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::ROLES)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $role = self::ROLE_CLIENT;

    /** Hashed password */
    #[ORM\Column]
    private ?string $password = null;

    /** Plain password from the request, hashed before saving; never stored or returned */
    #[Assert\Length(min: 6, max: 4096)]
    #[Groups(['user:write'])]
    #[SerializedName('password')]
    private ?string $plainPassword = null;

    /** Customer profile of a client (tickets are bought for it) */
    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['user:read'])]
    private ?Customer $customer = null;

    #[Assert\Callback]
    public function validatePassword(ExecutionContextInterface $context): void
    {
        if ($this->password === null && $this->plainPassword === null) {
            $context->buildViolation('This value should not be blank.')->atPath('password')->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * client → ROLE_CLIENT etc.; the hierarchy (admin > manager > client) is in security.yaml.
     */
    public function getRoles(): array
    {
        return ['ROLE_'.strtoupper((string) $this->role)];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

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

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
