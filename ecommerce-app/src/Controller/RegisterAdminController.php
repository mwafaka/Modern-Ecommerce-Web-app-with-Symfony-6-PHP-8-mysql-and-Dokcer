<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegisterAdminController extends AbstractController
{
    #[Route('/register-admin', name: 'app_register_admin')]
    public function register(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $user = new User();
        $user->setEmail('admin@gmail.com');
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $hasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new Response('Admin user created. You can now log in.');
    }
}
