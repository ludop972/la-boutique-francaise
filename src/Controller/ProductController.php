<?php

namespace App\Controller;

use App\Entity\Product;
use App\Classe\Search;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private entityManagerInterface $entityManagerInterface;

    public function __construct(entityManagerInterface $entityManagerInterface)
    {
        $this->entityManagerInterface = $entityManagerInterface;
    }

    #[Route('/nos-produits', name: 'app_products')]
    public function index(Request $request): Response
    {
        $search = new Search();
        $form = $this -> createForm(SearchType::class,$search);

        $form->handleRequest($request);

        if( ($form->isSubmitted()) && ($form->isValid())) {
            $products = $this->entityManagerInterface->getRepository(Product::class)->findWidthSearch($search);
        } else {
            $products = $this->entityManagerInterface->getRepository(Product::class)->findAll();

        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }

   #[Route('/produit/{slug}', name: 'app_product')]
   public function show($slug): Response
    {
        $product = $this->entityManagerInterface->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        $products = $this->entityManagerInterface->getRepository(Product::class)->findBy(['isBest' => 1]);

        if(!$product)
        {
            return $this->redirectToRoute('app_products');
        }

        return $this->render('/product/show.html.twig', [
            'product' => $product,
            'products' => $products
        ]);
    }
}
