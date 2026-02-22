<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Only admins can manage users
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => true,
            self::EDIT => true,
            self::DELETE => true,
            default => false,
        };
    }
}
