<?php

namespace App\Controller\Admin;

use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class OrderCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $crudUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $crudUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->crudUrlGenerator = $crudUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('updatePreparation','Préparation en cours','fas fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery','Livraison en cours','fas fa-truck')->linkToCrudAction('updateDelivery');

        return $actions
            ->add('detail',$updatePreparation)
            ->add('detail', $updateDelivery)
            ->add('index', 'detail');
    }

    public function updatePreparation(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(2);
        $this->entityManager->flush();

        $this->addFlash('notice', '<span style="color:green;"><strong>La commande' . $order->getReference() . ' est bien <u>en cours de préparation</u>.</strong></span>');
        $url = $this->crudUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        $mail = new Mail();
        $content = "Bonjour ".$order->getUser()->getFirstname()."<br>Commande en cours de préparation.<br><br>  	
                            Nous vous informons que votre commande est en cours de préparation. Nous vous tiendrons informé par e-mail lorsque les articles de votre commande auront été expédiés. 
                            Vous pouvez suivre l’état de votre commande ou modifier celle-ci dans votre espace membre";
        $mail->send($order->getUser()->getEmail(),$order->getUser()->getFullName(),'Votre commande La Boutique Française est en cours de préparation',$content);

        return $this->redirect($url);
    }

    public function updateDelivery(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(3);
        $this->entityManager->flush();

        $this->addFlash('notice', '<span style="color:orange;"><strong>La commande' . $order->getReference() . ' est bien <u>en cours de livraison</u>.</strong></span>');
        $url = $this->crudUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();


        $mail = new Mail();
        $content = "Bonjour ".$order->getUser()->getFirstname()."<br>Commande en cours de livraison.<br><br>  	
                            Nous vous informons que votre commande a été expédiée et qu'elle est en cours de livraison. 
                            Vous pouvez suivre l’état de votre commande ou modifier celle-ci dans votre espace membre";
        $mail->send($order->getUser()->getEmail(),$order->getUser()->getFullName(),'Votre commande La Boutique Française a été expédiée',$content);


        return $this->redirect($url);

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id'=> 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt', 'Passé le '),
            TextField::new('user.fullName', 'Utilisateur'),
            TextEditorField::new('delivery','Adresse de livraison')->onlyOnDetail(),
            MoneyField::new('total','Total produit')->setCurrency('EUR'),
            TextField::new('carrierName', 'Transporteur'),
            MoneyField::new('carrierPrice','Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non payée' => 0,
                'Payée' => 1,
                'Préparation en cours' => 2,
                'Livraison en cours' => 3
            ]),
            ArrayField::new('orderDetails','Produits achetées')->hideOnIndex()
        ];
    }

}
