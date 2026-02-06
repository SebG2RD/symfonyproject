<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostFormType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    #[Route('/posts', name: 'list_posts')]
    public function list(Request $request, PostRepository $postRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 9;
        $totalPosts = $postRepository->countAll();
        $totalPages = (int) ceil($totalPosts / $limit);

        if ($totalPages > 0 && $page > $totalPages) {
            return $this->redirectToRoute('list_posts', ['page' => $totalPages]);
        }

        $posts = $postRepository->findPaginated($page, $limit);

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPosts' => $totalPosts,
        ]);
    }

    #[Route('/posts/{id}', name: 'show_post', requirements: ['id' => '\\d+'])]
    public function show(int $id, PostRepository $postRepository, CommentRepository $commentRepository): Response
    {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $comments = $commentRepository->findApprovedByPost($post);
        $commentsCount = $commentRepository->countApprovedByPost($post);

        // Formulaire d'ajout de commentaire (affiché uniquement si connecté, voir template)
        $newComment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $newComment);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'commentsCount' => $commentsCount,
            'commentForm' => $commentForm,
        ]);
    }

    /**
     * Ajouter un commentaire sur un article (réservé aux utilisateurs connectés).
     */
    #[Route('/posts/{id}/comment', name: 'add_comment', methods: ['POST'], requirements: ['id' => '\\d+'])]
    #[IsGranted('ROLE_USER')]
    public function addComment(int $id, Request $request, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $comment->setPost($post);
        $comment->setStatus('pending');

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été envoyé et sera visible après validation.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('show_post', ['id' => $post->getId()]);
    }

    #[Route('/posts/create', name: 'create_post')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès!');
            return $this->redirectToRoute('show_post', ['id' => $post->getId()]);
        }

        return $this->render('post/create.html.twig', [
            'form' => $form,
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/posts/{id}/edit', name: 'edit_post', requirements: ['id' => '\\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(int $id, Request $request, PostRepository $postRepository, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $form = $this->createForm(PostFormType::class, $post, ['submit_label' => 'Mettre à Jour']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Article mis à jour avec succès!');
            return $this->redirectToRoute('show_post', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form,
            'post' => $post,
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/posts/{id}/delete', name: 'delete_post', methods: ['DELETE', 'POST'], requirements: ['id' => '\\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Article supprimé avec succès!');
        return $this->redirectToRoute('list_posts');
    }
}