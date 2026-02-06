<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des utilisateurs par l'administrateur :
 * liste des inscrits, éditer, activer/désactiver un compte.
 */
#[Route('/admin/users', name: 'admin_users_')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function edit(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour.');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Activer ou désactiver un compte utilisateur (bascule isActive).
     */
    #[Route('/{id}/toggle-active', name: 'toggle_active', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function toggleActive(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Éviter de se désactiver soi-même
        $currentUser = $this->getUser();
        if ($currentUser instanceof User && $user->getId() === $currentUser->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas désactiver votre propre compte.');
            return $this->redirectToRoute('admin_users_index');
        }

        $token = $request->getPayload()->getString('_token');
        if (!$this->isCsrfTokenValid('toggle_user_' . $user->getId(), $token)) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_users_index');
        }

        $user->setIsActive(!$user->getIsActive());
        $entityManager->flush();

        $message = $user->getIsActive() ? 'Compte activé.' : 'Compte désactivé.';
        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_users_index');
    }
}
