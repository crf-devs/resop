<?php

declare(strict_types=1);

namespace App\Entity;

interface AvailabilitableInterface
{
    public function getAvailabilities(): iterable;

    public function getId(): ?int;
}
