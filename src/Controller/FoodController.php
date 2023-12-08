<?php

namespace App\Controller;

use App\Entity\Food;
use App\Repository\FoodRepository;
use App\Repository\MealRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class FoodController extends AbstractController
{
    #[Route('/api/foods', name: 'app_foodAll', methods: ["GET"])]
    public function getAllFoods(Request $request, FoodRepository $foodRepository,SerializerInterface $serialiser, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $idCache = "getAllFoods-" . $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups(['getFoods']);
        $foods = $cachePool->get($idCache, function (ItemInterface $item ) use ($foodRepository, $page, $limit){
            $item->tag("foodCache");
            return $foodRepository->findAllWithPagination($page, $limit);
        });
        $jsonFood = $serialiser->serialize($foods, 'json', $context);
        return new JsonResponse(
            $jsonFood, Response::HTTP_OK, [], true
        );
    }

    #[Route("/api/foods/{id}", name: "app_foodOne", methods: "GET")]
    public function getOneFood(FoodRepository $foodRepository, int $id, SerializerInterface $serializer): JsonResponse
    {
        $food = $foodRepository->find($id);
        $context = SerializationContext::create()->setGroups(['getFoods']);
        if($food){
            $jsonFood = $serializer->serialize($food, "json", $context);
            return new JsonResponse($jsonFood, Response::HTTP_OK, [], true);
        }
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route("api/foods/{id}", name: "app_foodDelete", methods: ["DELETE"])]
    public function deleteFood(Food $food, FoodRepository $foodRepository, EntityManagerInterface $manager, int $id, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $food = $foodRepository->find($id);
        if($food){
            $manager->remove($food);
            $manager->flush();
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("api/foods", name: "app_foodCreate", methods: ["POST"])]
    public function createFood(UserService $user, ValidatorInterface $validator, EntityManagerInterface $manager, MealRepository $mealRepository, Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $food = $serializer->deserialize($request->getContent(), Food::class, "json");
        
        $errors = $validator->validate($food);
        if($errors->count() > 0 ){
            return new JsonResponse($serializer->serialize($errors, "json"), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();

        $idMeal = $content['meal_id'] ?? -1;
        $meal = $mealRepository->find($idMeal);
        $currentUser = $user->getCurrentUser();
        if(!$meal){
            return new JsonResponse("Meal not found", JsonResponse::HTTP_NOT_FOUND);
        }

        if($meal->getUser() !== $currentUser){
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $food->setMeal($mealRepository->find($idMeal));
        $manager->persist($food);
        $manager->flush();

        $context = SerializationContext::create()->setGroups(["getFoods"]);
        $jsonFood = $serializer->serialize($food, "json", $context);

        $location = $urlGenerator->generate("app_foodOne", ["id" => $food->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonFood, Response::HTTP_CREATED, ["Location" => $location], true);

    }

    #[Route('api/foods/{id}', name: "app_foodUpdate", methods: ["PUT"])]
    public function updateFood(Request $request, int $id,  MealRepository $mealRepository, FoodRepository $foodRepository, EntityManagerInterface $manager, SerializerInterface $serializer): JsonResponse
    {
        $currentFood = $foodRepository->find($id);
        $newFood = $serializer->deserialize($request->getContent(), Food::class, "json");
        $currentFood->setName($newFood->getName());
        $currentFood->setDescription($newFood->getDescription());
        $currentFood->setCalory($newFood->getCalory());
        $content = $request->toArray();
        $idMeal = $content["meal_id"];
        $currentFood->setMeal($mealRepository->find($idMeal));

        $manager->persist($currentFood);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }   

}
