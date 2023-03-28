<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/', name:'app_home')]
    public function index(): Response
    {
        return $this->render('blog/index.html.twig');
    }

    #[Route('/about')]
    public function aboutUs(): Response
    {
        return $this->render('blog/about.html.twig');
    }

}
