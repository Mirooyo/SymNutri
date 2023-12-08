<?php

namespace App\DataFixtures;

use App\Entity\Food;
use App\Entity\Meal;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        
        // Création d'un user "normal"
        $user = new User();
        $user->setEmail("user@bookapi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);
        
        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@bookapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

        for($i = 1; $i < 19; $i++){
            $meal = new Meal();
            $meal->setNom(rand(0, 3));
            $listMeal[] = $meal;
            $manager->persist($meal);
        }
        for($i = 0; $i < 19; $i++){
            $food = new Food();
            $food->setName("Aliment $i");
            $food->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque suscipit enim diam, consectetur gravida orci.m");
            $food->setCalory(rand(10, 300));
            $food->setMeal($listMeal[array_rand($listMeal)]);
            $manager->persist($food);
            
        }
        $manager->flush();
    }
}
