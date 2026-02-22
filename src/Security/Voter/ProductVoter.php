<?php

namespace App\Security\Voter;

use App\Entity\Product;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProductVoter extends Voter
{
    public const VIEW = 'PRODUCT_VIEW';
    public const CREATE = 'PRODUCT_CREATE';
    public const EDIT = 'PRODUCT_EDIT';
    public const DELETE = 'PRODUCT_DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE], true)) {
            return false;
        }

        if ($attribute === self::CREATE) {
            return true;
        }

        return $subject instanceof Product;
    }

    /**
     * @param Product|null $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!is_object($user)) {
            return false;
        }

        // Admins can do anything
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return true; // any authenticated user
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                return false; // only admin
        }

        return false;
    }
}
