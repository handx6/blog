<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    #[Route('/', name:'app_home')]
    public function index(PostRepository $repo): Response
    {
        // 1 - récupère tous les posts
        $posts = $repo->findAll();
        // 2 - Envoie la data à la vue
        return $this->render('blog/index.html.twig', compact('posts'));
    }

    #[Route('/post/{id}', name: 'app_show')]
    public function show($id, PostRepository $repo): Response
    {
        // Je récupère le post avec l'id
        $post = $repo->find($id);
        return $this->render('blog/show.html.twig', compact('post'));
    }

    #[Route('/post/delete/{id}', name: 'app_delete', methods: ['GET', 'DELETE'])]
    public function delete($id, PostRepository $repo, EntityManagerInterface $em): Response
    {
        // 1 - Je récupère le post avec l'id
        $post = $repo->find($id);
        // 2 - Je supprime le post
        $em->remove($post);
        // 3 - Tire la chasse
        $em->flush();
        // 4 - Redirection vers la page d'accueil
        return $this->redirectToRoute('app_home');
    }

    #[Route('/create', name: 'app_create')]
    public function create(PostRepository $repo): Response
    {
        // 1 - Create new object
        $post = new Post();
        // 2 - create form 
        $form = $this->createForm(PostFormType::class, $post);
        $showForm = $form->createView();

        // 3 - send form to view
        return $this->render('blog/create.html.twig', compact('showForm'));
    }
}
