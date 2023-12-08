<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Repository\MealRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MealController extends AbstractController
{
    #[Route('api/meals', name: 'app_mealGetAll', methods: "GET")]
    public function getAllMeals(UserService $user, MealRepository $mealRepository, SerializerInterface $serializer): JsonResponse
    {
        $currentUser = $user->getCurrentUser();
        if($currentUser){
            $mealList = $mealRepository->findBy(['user' => $currentUser]);
            $context = SerializationContext::create()->setGroups(["getMeals"]);
            $jsonMeal = $serializer->serialize($mealList, "json", $context);
            return new JsonResponse($jsonMeal, Response::HTTP_OK, [], true);
        }else {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
        }

    }

    #[Route('api/meals/{id}', name: "app_mealsGetOne", methods: ["GET"])]
    public function getOneMeals(MealRepository $mealRepository, SerializerInterface $serializer, int $id, UserService $user): JsonResponse
    {
        $meals = $mealRepository->find($id);
        
        $currentUser = $user->getCurrentUser();
        if($meals->getUser() !== $currentUser){
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }
        
        $context = SerializationContext::create()->setGroups(["getMeals"]);
        $jsonMeals = $serializer->serialize($meals, "json", $context);
        return new JsonResponse($jsonMeals, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('api/meals/{id}', name: "app_mealDelete", methods: ["DELETE"])]
    public function deleteMeal(EntityManagerInterface $manager, int $id, MealRepository $mealRepository): JsonResponse
    {
        $meal = $mealRepository->find($id);
        if($meal){
            $manager->remove($meal);
            $manager->flush();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    
    }

    #[Route('api/meals', name: "app_mealCreate", methods: ["POST"])]
    public function createMeal(Request $request, EntityManagerInterface $manager, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, UserService $user, UserRepository $userRepository)
    {
        $userId = $user->getCurrentUser()->getId();
        $currentUser = $userRepository->find($userId);
        $date = new \DateTimeImmutable();
        $meal = $serializer->deserialize($request->getContent(), Meal::class, "json");
        $meal->setUser($currentUser);
        $meal->setDate($date);
        $manager->persist($meal);
        $manager->flush();
        $jsonMeal = $serializer->serialize($meal, "json");

        return new JsonResponse(null, JsonResponse::HTTP_CREATED, [], true);
    }

    // Pas d'update car on change directement les aliments du repas
}
