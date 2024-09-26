<?php

namespace App\Service;

use App\Entity\Article;
use Stripe\Stripe;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class PaymentService
{
    private $domain;
    private $apiKey;
    private $em;
    public function __construct(protected ParameterBagInterface $parameter, private EntityManagerInterface $entityManagerInterface)
    {
        $this->parameter = $parameter;
        $this->apiKey = $this->parameter->get('STRIPE_API_SK');
        $this->domain = 'https://127.0.0.1:8000';
        $this->em = $entityManagerInterface;
    }

    public function askCheckout(string $id): ?Session
    {
        Stripe::setApiKey($this->apiKey); // Établissement de la connexion (requête API)        
        $checkoutSession = Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'tax_behavior' => 'exclusive',
                    'unit_amount' => 100, // Stripe utilise des centimes
                    'product_data' => [ // Les informations du produit sont personnalisables
                        'name' => 'PREMIUM',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->domain . '/payment-success/'.$id,
            'cancel_url' => $this->domain . '/payment-cancel',
            'automatic_tax' => [
                'enabled' => true,
            ],
        ]);

        return $checkoutSession;
    }
    //traitement du role de utilisateurs en fonction du paiement. 
    public function setPremiumStatus(): ?article
    {
        $article = new Article();
        $article->setPremium(true);

        $this->em->persist($article);
        $this->em->flush();
        return $article;
    }
}
