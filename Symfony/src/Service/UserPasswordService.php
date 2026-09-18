<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Hashes the plain password received from the API before the user is saved.
 */
final class UserPasswordService
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function hashPlainPassword(User $user): void
    {
        if ($user->getPlainPassword() !== null && $user->getPlainPassword() !== '') {
            $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
        }
    }
}
