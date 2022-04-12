<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/merci/{stripeSessionId}', name: 'app_order_success')]
    public function index($stripeSessionId, Cart $cart): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['stripeSessionId' => $stripeSessionId]);
        
        if(!$order || $order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if($order->getState() === 0){
            // Vider la session "cart"
            $cart->remove();

            //modifier le statut isPaid de notre commande en mettant 1
            $order->setState(1);
            $this->entityManager->flush();

            // Envoyer un email à notre client pour lui confirmer sa commande
            $mail = new Mail();
            $content = "Bonjour ".$order->getUser()->getFirstname()."<br>Merci pour votre commande.<br><br>  	
                            Nous vous remercions de votre commande. Nous vous tiendrons informé par e-mail lorsque les articles de votre commande auront été expédiés. 
                            Vous pouvez suivre l’état de votre commande ou modifier celle-ci dans votre espace membre";
            $mail->send(htmlentities($order->getUser()->getEmail()),htmlentities($order->getUser()->getFullName()),'Votre commande La Boutique Française est bien validée',htmlentities($content));
        }
        return $this->render('order_success/index.html.twig',[
            'order' => $order
        ]);
    }
}
