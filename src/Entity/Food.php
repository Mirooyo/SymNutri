<?php

namespace App\Entity;

use App\Repository\FoodRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FoodRepository::class)]
class Food
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getFoods"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getFoods", "getMeals"])]
    #[Assert\NotBlank(message: "L'aliment doit avoir un nom")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getFoods", "getMeals"])]
    #[Assert\Length(min: 10, minMessage: "La description doit faire minimum 10 caractères")]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(["getFoods", "getMeals"])]
    private ?float $calory = null;

    #[ORM\ManyToOne(inversedBy: 'food')]
    #[Groups(["getFoods"])]
    private ?Meal $meal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCalory(): ?float
    {
        return $this->calory;
    }

    public function setCalory(float $calory): static
    {
        $this->calory = $calory;

        return $this;
    }

    public function getMeal(): ?Meal
    {
        return $this->meal;
    }

    public function setMeal(?Meal $meal): static
    {
        $this->meal = $meal;

        return $this;
    }
}
