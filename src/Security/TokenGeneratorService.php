<?php

namespace App\Security;

class TokenGeneratorService
{
    /**
     * @throws \Exception
     * Génère une chaine de caractère aléatoire d'une taille définie.
     */
    public function generate(int $length = 25):string
    {
        return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
    }
}