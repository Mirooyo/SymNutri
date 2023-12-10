<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MealRepository::class)]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getFoods", "getMeals"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["getFoods", "getMeals"])]
    private ?int $nom = null;

    #[ORM\OneToMany(mappedBy: 'meal', targetEntity: Food::class, cascade: ['remove'])]
    #[Groups(["getMeals"])]
    private Collection $food;


    #[ORM\ManyToOne(inversedBy: 'meals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["getMeals"])]
    private ?\DateTimeInterface $date = null;

    public function __construct()
    {
        $this->food = new ArrayCollection();
        $this->date = \DateTimeImmutable::createFromFormat('Y-m-d', date('Y-m-d'));

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?int
    {
        return $this->nom;
    }

    public function setNom(int $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Food>
     */
    public function getFood(): Collection
    {
        return $this->food;
    }

    public function addFood(Food $food): static
    {
        if (!$this->food->contains($food)) {
            $this->food->add($food);
            $food->setMeal($this);
        }

        return $this;
    }

    public function removeFood(Food $food): static
    {
        if ($this->food->removeElement($food)) {
            // set the owning side to null (unless already changed)
            if ($food->getMeal() === $this) {
                $food->setMeal(null);
            }
        }

        return $this;
    }



    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
