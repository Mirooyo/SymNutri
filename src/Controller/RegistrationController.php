<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('api/register', name: 'app_register', methods: ["POST"])]
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, SerializerInterface $serializer)
    {
       $user = $serializer->deserialize($request->getContent(), User::class, "json"); 
       $password = $user->getPassword();
       $hashedPassword = $hasher->hashPassword($user, $password);
       $user->setPassword($hashedPassword);
       $user->setRoles(["ROLE_USER"]);
       $manager->persist($user);
       $manager->flush();
       return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }
}
