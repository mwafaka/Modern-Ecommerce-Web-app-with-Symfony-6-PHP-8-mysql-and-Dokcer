<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart_index')]
    public function index(CartService $cartService, EntityManagerInterface $em): Response
    {
        $cartItems = [];
        $total = 0;

        foreach ($cartService->getCart() as $id => $qty) {
            $product = $em->getRepository(Product::class)->find($id);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'subtotal' => $qty * $product->getPrice()
                ];
                $total += $qty * $product->getPrice();
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartItems,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    #[IsGranted('ROLE_USER')]
    public function add(Product $product, CartService $cartService): Response
    {
        $cartService->add($product);
        $this->addFlash('success', sprintf('"%s" added to cart.', $product->getTitle()));
        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(Product $product, CartService $cartService): Response
    {
        $cartService->remove($product);
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    #[IsGranted('ROLE_USER')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        return $this->redirectToRoute('app_cart_index');
    }
}
