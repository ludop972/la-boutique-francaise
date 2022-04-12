<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    private RequestStack $stack;
    private EntityManagerInterface $entityManagerInterface;

    public function __construct(RequestStack $stack, EntityManagerInterface $entityManagerInterface)
    {
        $this->stack = $stack;
        $this->entityManagerInterface = $entityManagerInterface;
    }

    public function add($id)
    {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []); // ici on cherche la session "cart"
        if(!empty($cart[$id])){
            $cart[$id] ++;
        }else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);
    }

    public function get()
    {
        $session = $this->stack->getSession();
        return $session->get('cart');
    }

    public function remove()
    {
        $methodRemove = $this->stack->getSession();
        return $methodRemove->remove('cart');
    }

    public function delete($id)
    {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        unset($cart[$id]);

        return $session->set('cart', $cart);
    }

    public function decrease($id)
    {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        if($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }

        return $session->set('cart', $cart);
    }

    public function getFull()
    {
        $cartComplete = [];
        if($this->get()){
            foreach($this->get() as $id => $quantity) {
                $productObject = $this->entityManagerInterface->getRepository(Product::class)->findOneBy(['id'=> $id]);
                if(!$productObject) {
                    $this->delete($id);
                    continue;
                }
                $cartComplete[] = [
                    'product' => $productObject,
                    'quantity' => $quantity,
                ];
            }
        }
        return $cartComplete;
    }
}