<?php

namespace App\Entity;

abstract class Entries
{
    public string $text;
    public array $pronunciations;
    public array $senses;
}
