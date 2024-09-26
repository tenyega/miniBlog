<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function all(ArticleRepository $ar): Response
    {
        $articles = $ar->findAll();
        return $this->render('article/all.html.twig', [
            'articles' => $articles,
        ]);
    }
}
