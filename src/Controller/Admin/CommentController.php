<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des commentaires par l'administrateur :
 * liste des commentaires, approuver, rejeter, supprimer.
 */
#[Route('/admin/comments', name: 'admin_comments_')]
#[IsGranted('ROLE_ADMIN')]
final class CommentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $comments = $commentRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $total = $commentRepository->count([]);
        $pages = ceil($total / $limit);

        return $this->render('admin/comment/index.html.twig', [
            'comments' => $comments,
            'currentPage' => $page,
            'totalPages' => $pages,
            'total' => $total,
        ]);
    }

    /**
     * Afficher les détails d'un commentaire.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id, CommentRepository $commentRepository): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire introuvable.');
        }

        return $this->render('admin/comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    /**
     * Supprimer un commentaire.
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function delete(int $id, Request $request, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire introuvable.');
        }

        $token = $request->getPayload()->getString('_token');
        if (!$this->isCsrfTokenValid('delete_comment_' . $comment->getId(), $token)) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_comments_index');
        }

        $postId = $comment->getPost()->getId();
        $entityManager->remove($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire supprimé.');

        return $this->redirectToRoute('admin_comments_index');
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function approve(int $id, Request $request, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire introuvable.');
        }

        $token = $request->getPayload()->getString('_token');
        if (!$this->isCsrfTokenValid('approve_comment_' . $comment->getId(), $token)) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_comments_index');
        }

        $comment->setStatus('approved');
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire approuve.');

        return $this->redirectToRoute('admin_comments_index');
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function reject(int $id, Request $request, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire introuvable.');
        }

        $token = $request->getPayload()->getString('_token');
        if (!$this->isCsrfTokenValid('reject_comment_' . $comment->getId(), $token)) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_comments_index');
        }

        $comment->setStatus('rejected');
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire rejete.');

        return $this->redirectToRoute('admin_comments_index');
    }

    /**
     * Filtrer les commentaires par statut.
     */
    #[Route('/filter/{status}', name: 'filter', methods: ['GET'], requirements: ['status' => 'pending|approved|rejected'])]
    public function filter(string $status, CommentRepository $commentRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $criteria = $status !== 'all' ? ['status' => $status] : [];
        
        $comments = $commentRepository->findBy($criteria, ['createdAt' => 'DESC'], $limit, $offset);
        $total = $commentRepository->count($criteria);
        $pages = ceil($total / $limit);

        return $this->render('admin/comment/index.html.twig', [
            'comments' => $comments,
            'currentPage' => $page,
            'totalPages' => $pages,
            'total' => $total,
            'filter' => $status,
        ]);
    }
}
