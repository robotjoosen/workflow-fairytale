<?php

declare(strict_types=1);

namespace App\Model;

class HeroStats {
    public function __construct(
        private readonly int $strength,
        private readonly int $charisma,
        private readonly int $dexterity,
        private readonly int $intelligence,
        private readonly int $stamina,
    ) {
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    public function getCharisma(): int
    {
        return $this->charisma;
    }

    public function getDexterity(): int
    {
        return $this->dexterity;
    }

    public function getIntelligence(): int
    {
        return $this->intelligence;
    }

    public function getStamina(): int
    {
        return $this->stamina;
    }
}