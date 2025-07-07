<?php

namespace App\Converter;

interface Convertable
{
    public static function convert(object $responseData);
}