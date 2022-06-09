<?php

namespace App\Event\User;

use App\Entity\User;

class UserCreatedEvent
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}