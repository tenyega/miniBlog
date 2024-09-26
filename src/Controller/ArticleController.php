<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article_all')]
    public function all(ArticleRepository $ar, PaginatorInterface $paginator, Request $request): Response
    {
        $articles = $ar->findAll();
        $pagination = $paginator->paginate(
            $articles, /* query NOT result in the form of an array or persistant collection and collection array  */
            $request->query->getInt('page', 1), /*page number, looks for a page variable inside the url*/
            10 /*limit per page*/
        );
        return $this->render('article/all.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_show')]
    public function show(ArticleRepository $ar, PaginatorInterface $paginator, Request $request, int $id): Response
    {
        $article = $ar->findOneBy(['id' => $id]);
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
