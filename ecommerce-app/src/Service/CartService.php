<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Product;

class CartService
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function add(Product $product): void
    {
        $cart = $this->session->get('cart', []);
        $id = $product->getId();

        if (!isset($cart[$id])) {
            $cart[$id] = 0;
        }

        $cart[$id]++;
        $this->session->set('cart', $cart);
    }

    public function remove(Product $product): void
    {
        $cart = $this->session->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    public function clear(): void
    {
        $this->session->remove('cart');
    }

    // src/Service/CartService.php

    public function getTotalQuantity(): int
    {
        $cart = $this->getCart();
        return array_sum($cart); // returns total quantity across all products
    }
}
