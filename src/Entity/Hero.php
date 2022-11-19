<?php

namespace App\Entity;

use App\Repository\HeroRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HeroRepository::class)]
class Hero
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $strength = null;

    #[ORM\Column]
    private ?int $stamina = null;

    #[ORM\Column]
    private ?int $dexterity = null;

    #[ORM\Column]
    private ?int $intelligence = null;

    #[ORM\Column]
    private ?int $charisma = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): void
    {
        $this->strength = $strength;
    }

    public function increaseStrength(): void
    {
        $this->strength += rand(0, 5);
    }

    public function decreaseStrength(): void
    {
        $this->strength -= rand(0, 5);
    }

    public function getStamina(): ?int
    {
        return $this->stamina;
    }

    public function setStamina(int $stamina): void
    {
        $this->stamina = $stamina;
    }

    public function increaseStamina(): void
    {
        $this->stamina += rand(0, 5);
    }

    public function decreaseStamina(): void
    {
        $this->stamina -= rand(0, 5);
    }

    public function getDexterity(): ?int
    {
        return $this->dexterity;
    }

    public function setDexterity(int $dexterity): void
    {
        $this->dexterity = $dexterity;
    }

    public function increaseDexterity(): void
    {
        $this->dexterity += rand(0, 5);
    }

    public function decreaseDexterity(): void
    {
        $this->dexterity -= rand(0, 5);
    }

    public function getIntelligence(): ?int
    {
        return $this->intelligence;
    }

    public function setIntelligence(int $intelligence): void
    {
        $this->intelligence = $intelligence;
    }

    public function increaseIntelligence(): void
    {
        $this->intelligence += rand(0, 5);
    }

    public function decreaseIntelligence(): void
    {
        $this->intelligence -= rand(0, 5);
    }

    public function getCharisma(): ?int
    {
        return $this->charisma;
    }

    public function setCharisma(int $charisma): void
    {
        $this->charisma = $charisma;
    }

    public function increaseCharisma(): void
    {
        $this->charisma += rand(0, 5);
    }

    public function decreaseCharisma(): void
    {
        $this->charisma -= rand(0, 5);
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->getName(), $this->getType());
    }
}
