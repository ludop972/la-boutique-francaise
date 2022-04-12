<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/compte/modifier-mon-mot-de-passe', name: 'app_account_password')]
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $notification_error = null;
        $notification_success = null;

        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $old_password = $form->get('old_password')->getData(); // on récupère l'ancien mot de passe du formulaire
            if($hasher->isPasswordValid($user, $old_password)) { //compare le mot de passe de l'user et celui tappé dans le formulaire
                $new_password = $form->get('new_password')->getData();
                $password = $hasher->hashPassword($user, $new_password);
                $user->setPassword($password);
                $this->entityManager->flush();
                $notification_success = "Votre mot de passe a bien été mis à jour";
            } else {
                $notification_error = "Votre mot de passe actuel n'est pas identique à celui que vous avez renseigné lors de votre inscription";
            }
        }

        return $this->render('/account/password.html.twig', [
            'form' => $form->createView(),
            'notification_error' => $notification_error,
            'notification_success' => $notification_success
        ]);
    }
}
