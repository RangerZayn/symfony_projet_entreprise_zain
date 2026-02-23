<?php

namespace App\Security\Voter;

use App\Entity\Client;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClientVoter extends Voter
{
    public const VIEW = 'CLIENT_VIEW';
    public const CREATE = 'CLIENT_CREATE';
    public const EDIT = 'CLIENT_EDIT';
    public const DELETE = 'CLIENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE])
            && ($subject === null || $subject instanceof Client);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Non connectés ne peuvent rien faire
        if (!is_object($user)) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($user),
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($user),
            self::DELETE => $this->canDelete($user),
            default => false,
        };
    }

    private function canView($user): bool
    {
        // Seuls les ROLE_ADMIN et ROLE_MANAGER peuvent voir les clients
        return in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_MANAGER', $user->getRoles());
    }

    private function canCreate($user): bool
    {
        // Seuls les ROLE_ADMIN et ROLE_MANAGER peuvent créer des clients
        return in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_MANAGER', $user->getRoles());
    }

    private function canEdit($user): bool
    {
        // Seuls les ROLE_ADMIN et ROLE_MANAGER peuvent modifier les clients
        return in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_MANAGER', $user->getRoles());
    }

    private function canDelete($user): bool
    {
        // Seuls les ROLE_ADMIN peuvent supprimer les clients
        return in_array('ROLE_ADMIN', $user->getRoles());
    }
}
