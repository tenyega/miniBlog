<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Repository\ArticleRepository;
use App\Service\EmailNotificationService;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/premium/{id}', name: 'app_article_premium')]
    public function goPremium(PaymentService $ps, string $id): Response
    {

        return $this->redirect($ps->askCheckout($id)->url);
    }


    #[Route('/payment-success/{id}', name: 'app_payment_success', methods: ['GET'])]
    public function paymentSuccess(Request $request, string $id, ArticleRepository $ar): Response
    {
        if ($request->headers->get('referer') === 'https://checkout.stripe.com/') {
            $article = $ar->findOneBy(['id' => $id]);
            return $this->render('article/payment-success.html.twig', [
                'article' => $article
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/payment-cancel', name: 'app_payment_cancel')]
    public function paymentCancel(Request $request): Response
    {
        if ($request->headers->get('referer') === 'https://checkout.stripe.com/') {
            return $this->render('article/payment-cancel.html.twig');
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/subscribe', name: 'app_subscribe')]
    public function subscribe(): Response
    {
        return $this->render('article/subscribe.html.twig');
    }

    #[Route('/subscribe/addEmail', name: 'app_subscribe_addEmail')]
    public function addEmail(Request $request, EntityManagerInterface $em, EmailNotificationService $ens): Response
    {
        $subscriber = new Subscriber();
        $emailUser = $request->get('email');
        if ($emailUser) {
            $subscriber->setEmail($emailUser);
            $em->persist($subscriber);
            $em->flush();

            $ens->sendEmail($emailUser);
            return $this->render('article/subscriptionNotification.html.twig');
        }
    }
}
