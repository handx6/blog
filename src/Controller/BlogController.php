<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/', name:'app_home')]
function index(PostRepository $repo): Response
    {
    // 1 - récupère tous les posts
    $posts = $repo->findAll();
    // 2 - Envoie la data à la vue
    return $this->render('blog/index.html.twig', compact('posts'));
}

#[Route('/post/{id}', name:'app_show')]
function show($id, PostRepository $repo): Response
    {
    // Je récupère le post avec l'id
    $post = $repo->find($id);
    return $this->render('blog/show.html.twig', compact('post'));
}

#[Route('/post/delete/{id}', name:'app_delete', methods:['GET', 'DELETE'])]
function delete($id, PostRepository $repo, EntityManagerInterface $em): Response
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

#[Route('/create', name:'app_create', methods:['GET', 'POST'])]
function create(Request $request, EntityManagerInterface $em): Response
    {
    // 1 - Create new object
    $post = new Post();
    // 2 - create form
    $form = $this->createForm(PostFormType::class, $post);
    //$showForm = $form->createView();
    //Add post in db
    // fetch data from input
    $form->handleRequest($request);
    // submit form
    if ($form->isSubmitted() && $form->isValid()) {
        // stock data from user
        $newPost = $form->getData();
        // check if a pic has been chosen
        $imgPath = $form->get('url_img')->getData();

        if ($imgPath) {
            $newFileName = uniqid() . '.' . $imgPath->guessExtension();
            try {
                // move picture in public/upload dir
                $imgPath->move(
                    $this->getParameter('kernel.project_dir') . '/public/upload',
                    $newFileName
                );
            } catch (FileException $e) {
                return new Response($e->getMessage());
            }
            // send url to db
            $newPost->setUrlImg('/upload/' . $newFileName);
        }
        $newPost->setCreatedAt(new DateTimeImmutable());
        // persists data from user entries
        $em->persist($newPost);
        $em->flush();

        // redirection
        return $this->redirectToRoute('app_home');
    }
    // 3 - send form to view
    return $this->render('blog/create.html.twig', [
        'showForm' => $form->createView(),
    ]);
}
#[Route('/update/{id}', name:'app_update', methods:['GET', 'POST'])]
function update($id, PostRepository $repo, Request $request, EntityManagerInterface $em): Response
    {
    // Je récupère le post avec l'id
    $post = $repo->find($id);

    // 2 - create form
    $form = $this->createForm(PostFormType::class, $post);
    //$showForm = $form->createView();
    //Add post in db
    // fetch data from input
    $form->handleRequest($request);
    // fetch url img from db
    $imgPath = $form->get('url_img')->getData();

    // submit form
    if ($form->isSubmitted() && $form->isValid()) {
        // stock data from user
        // check if a pic has been chosen
        if ($imgPath) {
            if ($post->getUrlImg() !== null) {
                $newFileName = uniqid() . '.' . $imgPath->guessExtension();
                try {
                    // move picture in public/upload dir
                    $imgPath->move(
                        $this->getParameter('kernel.project_dir') . '/public/upload',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }
            }
            // send url to db
            $post->setUrlImg('/upload/' . $newFileName);
            $post->setUpdatedAt(new DateTimeImmutable());
            $em->flush();
            return $this->redirectToRoute('app_home');
        }
        
        $post->setUpdatedAt(new DateTimeImmutable());
        $em->flush();
        return $this->redirectToRoute('app_home');

    }
    // 3 - send form to view
    return $this->render('blog/update.html.twig', [
        'showForm' => $form->createView(),
    ]);
}
}
