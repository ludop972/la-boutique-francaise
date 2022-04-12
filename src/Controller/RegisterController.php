<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Form\RegisterType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('/inscription', name: 'app_register')]
    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $notificationSuccess = null;
        $notificationError = null;

        $user = new User(); //objet créé a partir de la classe User

        $form = $this->createForm(RegisterType::class,$user); // on créé un formulaire grace a createForm qu'on stocke dans une variable

        $form->handleRequest($request); // on intérroge la base de donnée

        if($form->isSubmitted() && $form->isValid()) { //condition : si le formulaire d'inscription est soumis et valide

            $user = $form->getData(); // on récupère les données

            $search_email = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]); //variable pour aller chercher l'email de l'user en base de données

            if(!$search_email) { // si l'email renseignée n'est pas en base de données
                $password = $encoder->hashPassword($user, $user->getPassword()); // on encode le mot de passe
                $user->setPassword($password);

                $this->entityManager->persist($user); // on persiste les données donc on les figes

                $this->entityManager->flush(); //on les enregistre en base de données

                $mail = new Mail();

                $content = "Bonjour ".$user->getFirstname()."<br>Bienvenue sur la première boutique dédiée au made in France.<br><br>  	
                            Merci de nous avoir envoyé vos informations de compte. Vous pouvez désormais effectuer des achats sur notre site. 	 
 	                        Vous pouvez consulter vos commandes ainsi que d'autres informations concernant votre compte dans votre espace membre.";


                $mail->send(htmlentities($user->getEmail()),htmlentities($user->getFullName()),'Bienvenue sur La Boutique Française',htmlentities($content));
                $notificationSuccess = 'Votre inscription s\'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte.';

            } else {
                $notificationError = 'L\'email que vous avez renseigné existe déjà';
            }



        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),   // on déclare a la vue qu'il faut injecter form
            'notification_error' => $notificationError,
            'notification_success' => $notificationSuccess
        ]);
    }

}
