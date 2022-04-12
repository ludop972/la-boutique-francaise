<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountAddressController extends AbstractController
{
    private EntityManagerInterface $entityManagerInterface;

    /**
     * @param EntityManagerInterface $entityManagerInterface
     */
    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManagerInterface = $entityManagerInterface;
    }

    #[Route('/compte/adresses', name: 'app_account_address')]
    public function index(): Response
    {
        return $this->render('/account/address.html.twig');
    }

    #[Route('/compte/ajouter-une-adresse', name: 'app_account_address_form')]
    public function add(Cart $cart , Request $request): Response
    {
        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $address->setUser($this->getUser());
            $this->entityManagerInterface->persist($address);
            $this->entityManagerInterface->flush();
            if($cart->get())
            {
                return $this->redirectToRoute('app_order');
            }else {
                return $this->redirectToRoute('app_account_address');

            }
        }
        return $this->render('/account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/modifier-une-adresse/{id}', name: 'app_account_address_edit')]
    public function edit(Request $request, $id): Response
    {
        $address = $this->entityManagerInterface->getRepository(Address::class)->findOneBy(['id'=>$id]);

        if(!$address || $address->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_account_address');
        }


        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManagerInterface->flush();
            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('/account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/supprimer-une-adresse/{id}', name: 'app_account_address_delete')]
    public function delete( $id): Response
    {
        $address = $this->entityManagerInterface->getRepository(Address::class)->findOneBy(['id'=>$id]);

        if($address && $address->getUser() === $this->getUser()) {
            $this->entityManagerInterface->remove($address);
            $this->entityManagerInterface->flush();
        }
            return $this->redirectToRoute('app_account_address');
    }
}
