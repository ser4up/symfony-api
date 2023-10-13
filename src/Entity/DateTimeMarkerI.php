<?php

namespace App\Entity;

interface DateTimeMarkerI
{
    public function getCreatedDate(): ?\DateTimeInterface;

    public function setCreatedDate(\DateTimeInterface $createdDate): static;

    public function getUpdatedDate(): ?\DateTimeInterface;

    public function setUpdatedDate(\DateTimeInterface $updatedDate): static;
}
