<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Vérifie avant la connexion que le compte utilisateur est actif.
 * Si l'admin a désactivé le compte (isActive = false), la connexion est refusée.
 */
class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsActive()) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été désactivé. Contactez l\'administrateur.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Rien à vérifier après authentification
    }
}
