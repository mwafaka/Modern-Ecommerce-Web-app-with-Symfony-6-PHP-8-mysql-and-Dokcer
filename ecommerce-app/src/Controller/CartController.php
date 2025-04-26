<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\CartItem;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart_index')]
    #[IsGranted('ROLE_USER')]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCartItems();

        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item->getQuantity() * $item->getPriceAtTime();
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartItems,
            'total' => $total
        ]);
    }


    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    #[IsGranted('ROLE_USER')]
    public function add(Product $product, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User is not authenticated or wrong user class.');
        }
        $cartItem = $em->getRepository(CartItem::class)->findOneBy([
            'user' => $user,
            'product' => $product
        ]);

        if (!$cartItem) {
            $cartItem = new CartItem();
            $cartItem->setUser($user);
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
            $cartItem->setPriceAtTime($product->getPrice());

            $em->persist($cartItem);
        } else {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        }

        $em->flush();

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
