<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserService
{
    private $tokenStorageInterface;
    private $jwtManager;
    private $entityManager;

    public function __construct(TokenStorageInterface $tokenStorage, JWTTokenManagerInterface $jwtTokenManager, EntityManagerInterface $manager)
    {
        $this->tokenStorageInterface = $tokenStorage;
        $this->jwtManager = $jwtTokenManager;
        $this->entityManager = $manager;
    }

    public function getCurrentUser()
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());

        
        if (!$decodedJwtToken) {
            return null;
        }

        $userId = $this->getUserIdFromEmail($decodedJwtToken['username']);

        $user = $this->entityManager->getRepository(User::class)->find($userId);

        return $user;

         
    }

    public function getUserIdFromEmail($email)
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user) {
            return $user->getId();
        }

        return null;
    }

}
